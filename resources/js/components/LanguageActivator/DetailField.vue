<template>
    <div>
		<component
			v-if="inited"
			:is="'detail-boolean-group-field'"
			:resource-id="resourceId"
			:resource-name="resourceName"
			:field="customField"
			:ref="'field-' + field.attribute"
		/>
		<div />
    </div>
</template>

<script>
    export default {
        props: ['resource', 'resourceName', 'resourceId', 'field'],

        data() {
            return {
                currentLocale: null,
                locales: this.field.locales,
                fields: this.field.fields,
				customField: this.field,
				inited: false
            }
        },

		/**
		 * Mount the component.
		 */
		mounted() {
			Object.values(this.fields).forEach(f => {
				if (f.value) {
					if (!this.customField.value) {
						this.customField.value = {}
					}
					this.customField.value[f.attribute] = f.value
				}
			});
			this.inited = true
		}
    }
</script>
