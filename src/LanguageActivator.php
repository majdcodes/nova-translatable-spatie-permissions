<?php

namespace Kreatorij\Nova\Fields;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class LanguageActivator extends Field
{
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'language-activator';

    private $locales = [];

    /** @var Field */
    private $field;

    /** @var array */
    private $fields;

	/**
	 * The options for the field.
	 *
	 * @var array
	 */
	public $options;

    /**
     * Create a new field.
     *
     * @param Field $field
     */
    public function __construct(Field $field)
    {
        parent::__construct($field->name, $field->attribute, $field->resolveCallback);

        $locales = app('translatable.locales')->all();
        $this->locales = array_combine($locales, array_map(function ($value){
        	return strtoupper($value);
		}, $locales));
        $this->field = $field;

        $fields = array_map(function ($locale) use ($field) {
        	return $this->localizeField(clone $field, $locale);
		}, $locales);
        $this->fields = array_combine($locales, $fields);

		$options = [];
		foreach ($fields as $field_item) {
			$options[$field_item->attribute] = strtoupper($field_item->locale);
        }
		$this->options($options);

        $this->withMeta([
            'locales' => $this->locales,
            'fields' => $this->fields,
            'originalField' => $this->field,
			'options' => $this->options
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
     * @param string|null $attribute
     * @return void
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
     * @param  mixed  $resource
     * @param  string  $attribute
     * @return mixed
     */
    protected function resolveAttribute($resource, $attribute)
    {
        return $resource->translations->pluck($attribute, config('translatable.locale_key'));
    }

    protected function localizeField(Field $field, string $locale)
    {
        $field->attribute = $this->localizeAttribute($locale, $field->attribute);
		$field->locale = $locale;

        return $field;
    }

    protected function localizeAttribute(string $locale, string $attribute = null)
    {
        return is_null($attribute) ? null : "translatable_{$locale}_{$attribute}";
    }

	/**
	 * Set the options for the field.
	 *
	 * @param  array|\Closure|\Illuminate\Support\Collection
	 * @return $this
	 */
	public function options($options)
	{
		if (is_callable($options)) {
			$options = $options();
		}

		$this->options = with(collect($options), function ($options) {
			return $options->map(function ($label, $name) use ($options) {
				return $options->isAssoc()
					? ['label' => $label, 'name' => $name]
					: ['label' => $label, 'name' => $label];
			})->values()->all();
		});

		return $this;
	}

	/**
     * Hydrate the given attribute on the model based on the incoming request.
     *
     * @param NovaRequest $request
     * @param  string  $requestAttribute
     * @param  Model  $model
     * @param  string  $attribute
     * @return void
     */
    protected function fillAttributeFromRequest(NovaRequest $request,
												$requestAttribute,
												$model,
												$attribute
	)
    {
        /** @var array $requestData */
        $requestData = $request->all();
		$requestDataFixed = json_decode($requestData[$requestAttribute], true);

		foreach ($this->locales as $localeCode => $locale) {
			$value = $requestDataFixed[$this->localizeAttribute($localeCode, $requestAttribute)];

			if ($value === 'true') {
				$value = true;
			}

			if (is_null($value)){
				continue;
			}

			if ($model->relationLoaded('translation')) {
				$model->unsetRelation('translation');
			}

			$model->translate($localeCode)->{$requestAttribute} = $value;
		}
    }
}
