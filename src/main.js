import Vue from 'vue'
import App from './views/App.vue'

import router from './router/index.js'
import Router from 'vue-router'
import mitt from 'mitt'

// ⬇️ IMPORTA funciones de l10n
import { loadTranslations, translate as t, translatePlural as n } from '@nextcloud/l10n'

Vue.use(Router)
// ⬇️ Expón t/n a todos los componentes (para usarlos en templates y methods)
Vue.mixin({ methods: { t, n } })

// Agregar OC y OCA a Vue para acceso global
Vue.prototype.OC = window.OC
Vue.prototype.OCA = window.OCA

// Obtener configuraciones del módulo desde el DOM
const dataElement = document.getElementById('data')
const configuraciones = dataElement ? JSON.parse(dataElement.getAttribute('data-parameters') || '{}') : {}

const groupElement = document.getElementById('group-user')
const groups = groupElement ? JSON.parse(groupElement.getAttribute('data-parameters') || '{}') : {}

const employeeElement = document.getElementById('employee')
const employee = employeeElement ? JSON.parse(employeeElement.getAttribute('data-parameters') || '[]') : []

const subordinatesElement = document.getElementById('subordinates')
const subordinates = subordinatesElement ? JSON.parse(subordinatesElement.getAttribute('data-parameters') || '[]') : []

const emitter = mitt()
Vue.prototype.$bus = emitter

loadTranslations('empleados').then(() => {
	const View = Vue.extend(App)
	new View({
		router,
		propsData: {
			parameters: configuraciones,
			groupsUser: groups,
			employee,
			subordinatesGroup: subordinates,
		},
	}).$mount('#content')
})
