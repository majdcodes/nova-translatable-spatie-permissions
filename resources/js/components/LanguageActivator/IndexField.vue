<template>

	<div>
		<span
			v-for="(field_locale, locale) in field.fields"
			:key="locale"
			v-text="locale"
			class="text-xs uppercase pr-1 text-gray-400"
			:class="{ 'font-bold': field_locale.value, 'text-gray-800': field_locale.value }"
		></span>
	</div>

</template>

<script>
    export default {
        props: ['resourceName', 'field'],

        data() {
            return {
                originalField: this.field.originalField,
                indexLocale: this.field.indexLocale,
	            locales: this.field.locales,
            }
        },

        computed: {
            localizedField(){
                return this.field.fields[this.indexLocale];
            }
        },

    }
</script>
<style lang="scss">
.pr-1 {
	& + .pr-1 {
		margin-left: 2px;
	}
}
</style>
