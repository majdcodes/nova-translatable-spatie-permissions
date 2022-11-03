<template>
    <div>
        <div v-if="locales.length > 1" class="w-full pt-2 px-8 relative z-10 bullup">
            <a
                class="inline-block cursor-pointer mr-2 animate-text-color select-none text-xs"
                :class="{ 'text-60': localeKey !== currentLocale, 'text-primary': localeKey === currentLocale, 'font-bold': localeKey === currentLocale }"
                :key="`a-${localeKey}`"
                v-for="(locale, localeKey) in locales"
                @click="changeLocale(localeKey)"
				v-text="locale"
            />
        </div>

        <template
			v-for="(localizedField, localeKey) in fields"
			:key="localeKey"
		>
            <component
                v-show="localeKey === currentLocale"
                :is="`form-${localizedField.component}`"
                :resource-id="resourceId"
                :resource-name="resourceName"
                :field="localizedField"
                :ref="'field-' + localizedField.attribute"
            />
			<div />
        </template>
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
                    let field = this.$refs['field-' + f.attribute][0];
                    field.setInitialValue();
                });
            },

            changeLocale(locale) {
				this.emitter.emit('change-locale', locale)
                this.currentLocale = locale;
            },

			syncChangeLocale(locale) {
				this.currentLocale = locale;
			},

            /**
             * Fill the given FormData object with the field's internal value.
             */
            fill(formData) {
                Object.values(this.fields).forEach(f => {
                    let field = this.$refs['field-' + f.attribute][0];
                    field.fill(formData);
                });
            },
        },

		mounted() {
			this.currentLocale = Object.keys(this.locales)[0] || null;
			this.emitter.on('change-locale', this.syncChangeLocale)
		},
		beforeDestroy() {
			this.emitter.off('change-locale')
		}
	}
</script>
<style>
.bullup {
	margin-bottom: -24px;
}
</style>
