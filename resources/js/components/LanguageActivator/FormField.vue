<template>
    <div>
		<component
			:is="'form-boolean-group-field'"
			:resource-id="resourceId"
			:resource-name="resourceName"
			:field="field"
			:ref="'field-' + field.attribute"
		/>
		<div />
    </div>
</template>

<script>

import { FormField, HandlesValidationErrors } from 'laravel-nova'

export default {
	mixins: [FormField, HandlesValidationErrors],

	props: ['resourceName', 'resourceId', 'field'],

	data() {
		return {
			locales: this.field.locales,
			currentLocale: null,
			fields: this.field.fields,
			originalField: this.field.originalField,
		}
	},

	methods: {
		/**
		 * Set the initial, internal value for the field.
		 */
		setInitialValue() {
			Object.values(this.fields).forEach(f => {
				if (this.field.value === null) {
					this.field.value = {}
				}
				this.field.value[f.attribute] = f.value
			});
			this.$refs['field-' + this.field.attribute].setInitialValue();
		},

		/**
		 * Fill the given FormData object with the field's internal value.
		 */
		fill(formData) {
			let field = this.$refs['field-' + this.field.attribute];
			field.fill(formData);
		},
	},
}

</script>
