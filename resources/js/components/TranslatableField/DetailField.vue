<template>
    <div>
		<div class="w-full pt-2 -mb-2 relative z-10">
			<a
				class="inline-block cursor-pointer mr-2 animate-text-color select-none text-xs"
				:class="{ 'text-60': localeKey !== currentLocale, 'text-primary': localeKey === currentLocale, 'font-bold': localeKey === currentLocale }"
				:key="`a-${localeKey}`"
				v-for="(locale, localeKey) in locales"
				@click="changeLocale(localeKey)"
				v-text="locale"
			/>
		</div>

        <template v-for="(originalField, localeKey) in field.fields">
            <component
                v-show="localeKey === currentLocale"
                :is="'detail-' + originalField.component"
                :field="originalField"
                :resource-id="originalField.resourceId"
                :resource-name="originalField.resourceName"
            />
			<div />
        </template>
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
            }
        },

        methods: {
            changeLocale(locale) {
                if 	(this.currentLocale !== locale){
					this.emitter.emit('change-locale', locale)
                    this.currentLocale = locale;
                }
            },

			syncChangeLocale(locale) {
				this.currentLocale = locale;
			},
        },

		/**
		 * Mount the component.
		 */
		mounted() {
			this.currentLocale = Object.keys(this.locales)[0] || null;
			this.emitter.on('change-locale', this.syncChangeLocale)
		},
		beforeDestroy() {
			this.emitter.off('change-locale')
		}
    }
</script>
