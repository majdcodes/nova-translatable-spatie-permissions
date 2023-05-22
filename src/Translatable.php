<?php

namespace Kreatorij\Nova\Fields;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class Translatable extends Field
{
	/**
	 * The field's component.
	 *
	 * @var string
	 */
	public $component = 'translatable-field';

	private $locales = [];

	/** @var Field */
	private $field;

	/** @var array */
	private $fields;

	/**
	 * Create a new field.
	 */
	public function __construct(Field $field)
	{
		parent::__construct($field->name, $field->attribute, $field->resolveCallback);

		$locales = app('translatable.locales')->all();
		$this->locales = array_combine($locales, array_map(function ($value) {
			return strtoupper($value);
		}, $locales));
		$this->field = $field;

		$fields = array_map(function ($locale) use ($field) {
			return $this->localizeField(clone $field, $locale);
		}, $locales);
		$this->fields = array_combine($locales, $fields);

		$this->withMeta([
			'locales' => $this->locales,
			'fields' => $this->fields,
			'originalField' => $this->field,
		]);

		$this->indexLocale(app()->getLocale());

		$this->showOnIndex = $this->field->showOnIndex;
		$this->showOnDetail = $this->field->showOnDetail;
		$this->showOnCreation = $this->field->showOnCreation;
		$this->showOnUpdate = $this->field->showOnUpdate;
	}

	public function indexLocale($locale)
	{
		return $this->withMeta(['indexLocale' => $locale]);
	}

	/**
	 * @param \Astrotomic\Translatable\Contracts\Translatable $resource
	 * @param null|string                                     $attribute
	 */
	public function resolve($resource, $attribute = null)
	{
		$defaultLocale = $resource->getDefaultLocale();

		/** @var Field $field */
		foreach ($this->fields as $localeCode => $field) {
			$resource->setDefaultLocale($localeCode);
			$field->resolve($resource, $this->field->attribute);
		}

		$resource->setDefaultLocale($defaultLocale);
	}

	/**
	 * Resolve the given attribute from the given resource.
	 *
	 * @param mixed  $resource
	 * @param string $attribute
	 *
	 * @return mixed
	 */
	protected function resolveAttribute($resource, $attribute)
	{
		return $resource->translations->pluck($attribute, config('translatable.locale_key'));
	}

	protected function localizeField(Field $field, string $locale)
	{
		$field->attribute = $this->localizeAttribute($locale, $field->attribute);

		return $field;
	}

	protected function localizeAttribute(string $locale, string $attribute = null)
	{
		return is_null($attribute) ? null : "translatable_{$locale}_{$attribute}";
	}

	/**
	 * Hydrate the given attribute on the model based on the incoming request.
	 *
	 * @param string $requestAttribute
	 * @param Model  $model
	 * @param string $attribute
	 */
	protected function fillAttributeFromRequest(NovaRequest $request, $requestAttribute, $model, $attribute)
	{
		/** @var array $requestData */
		$requestData = $request->all();

		foreach ($this->locales as $localeCode => $locale) {
			$value = $request->get($this->localizeAttribute($localeCode, $requestAttribute));

			if ('true' === $value) {
				$value = true;
			}

			// Flexible content hack!
			if (is_array($value) && !empty($value)) {
				foreach ($value as &$subitem) {
					if (!empty($subitem['layout'])) {
						$key = $subitem['key'];
						foreach ($subitem['attributes'] as $bad_key => $attribute_item) {
							if (false !== stripos($bad_key, $key)) {
								$new_key = explode('__', $bad_key);
								$subitem['attributes'][$new_key[1]] = $attribute_item;
								unset($subitem['attributes'][$bad_key]);
							}
						}
					} else {
						break;
					}
				}
			}

			if (!empty($value) && is_string($value) && is_array(json_decode($value, true))) {
				$value = json_decode($value);

				if (is_object($value)) {
					$value = json_encode($value);
				}
			}

			//            if (is_null($value)){
			//                continue;
			//            }
			//            $model->setDefaultLocale($localeCode);

			if ($model->relationLoaded('translation')) {
				$model->unsetRelation('translation');
			}

			$model->translateOrNew($localeCode)->{$requestAttribute} = $value;
		}
	}
}
