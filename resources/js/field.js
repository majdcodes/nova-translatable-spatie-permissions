import TranslatableFieldIndexField from './components/TranslatableField/IndexField'
import TranslatableFieldDetailField from './components/TranslatableField/DetailField'
import TranslatableFieldFormField from './components/TranslatableField/FormField'

import LanguageActivatorIndexField from './components/LanguageActivator/IndexField'
import LanguageActivatorDetailField from './components/LanguageActivator/DetailField'
import LanguageActivatorFormField from './components/LanguageActivator/FormField'

import mitt from 'mitt';
const emitter = mitt();

Nova.booting((app, store) => {
	// Translatable field
	app.component('index-translatable-field', TranslatableFieldIndexField)
	app.component('detail-translatable-field', TranslatableFieldDetailField)
	app.component('form-translatable-field', TranslatableFieldFormField)

	// Language activator
	app.component('index-language-activator', LanguageActivatorIndexField)
	app.component('detail-language-activator', LanguageActivatorDetailField)
	app.component('form-language-activator', LanguageActivatorFormField)

	app.config.globalProperties.emitter = emitter;
})
