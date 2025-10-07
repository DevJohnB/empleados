<template>
	<NcAppNavigation>
		<!-- General -->
		<NcAppNavigationCaption
			:heading-id="t('empleados', 'General')"
			is-heading
			:name="t('empleados', 'General')" />
		<NcAppNavigationList :aria-labelledby="t('empleados', 'General')">
			<NcAppNavigationItem
				:name="t('empleados', 'Home')"
				:to="{ name: 'Home' }"
				exact />
		</NcAppNavigationList>

		<!-- Capital Humano (solo admin / RH) -->
		<div v-if="isAdmin()">
			<NcAppNavigationCaption
				:heading-id="t('empleados', 'Human Resources')"
				is-heading
				:name="t('empleados', 'Human Resources')" />
			<NcAppNavigationList :aria-labelledby="t('empleados', 'Human Resources')">
				<NcAppNavigationItem
					:name="t('empleados', 'Employees')"
					:to="{ name: 'Empleados' }" />
				<NcAppNavigationItem
					:name="t('empleados', 'Areas / Departments')"
					:to="{ name: 'Areas' }" />
				<NcAppNavigationItem
					:name="t('empleados', 'Positions')"
					:to="{ name: 'Puestos' }" />
				<NcAppNavigationItem
					:name="t('empleados', 'Teams')"
					:to="{ name: 'Equipos' }" />
			</NcAppNavigationList>
		</div>

		<!-- Ahorro -->
		<div v-if="ahorroModulo()">
			<NcAppNavigationCaption
				:heading-id="t('empleados', 'Savings module')"
				is-heading
				:name="t('empleados', 'Savings module')" />
			<NcAppNavigationList :aria-labelledby="t('empleados', 'Savings module')">
				<NcAppNavigationItem
					:name="t('empleados', 'Request')"
					:to="{ name: 'Ahorros' }" />
			</NcAppNavigationList>
			<NcAppNavigationList v-if="isAdmin()" :aria-labelledby="t('empleados', 'Savings module')">
				<NcAppNavigationItem
					:name="t('empleados', 'Admin panel')"
					:to="{ name: 'PanelAhorros' }" />
			</NcAppNavigationList>
		</div>

		<!-- Ausencias -->
		<div v-if="ausenciasModulo()">
			<NcAppNavigationCaption
				:heading-id="t('empleados', 'Working time')"
				is-heading
				:name="t('empleados', 'Working time')" />
			<NcAppNavigationList :aria-labelledby="t('empleados', 'Working time')">
				<NcAppNavigationItem
					:name="t('empleados', 'Calendar')"
					:to="{ name: 'Calendario' }" />
			</NcAppNavigationList>
		</div>
	</NcAppNavigation>
</template>

<script>
import {
	NcAppNavigation,
	NcAppNavigationItem,
	NcAppNavigationList,
	NcAppNavigationCaption,
} from '@nextcloud/vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Sidenavigation',
	components: {
		NcAppNavigation,
		NcAppNavigationItem,
		NcAppNavigationList,
		NcAppNavigationCaption,
	},
	inject: ['groupuser', 'configuraciones'],
	methods: {
		t, // expone t al template
		navigateTo(route) {
			this.$router.push({ name: route })
		},
		isAdmin() {
			return 'admin' in this.groupuser || 'recursos_humanos' in this.groupuser
		},
		ahorroModulo() {
			return this.configuraciones.modulo_ahorro === 'true'
		},
		ausenciasModulo() {
			return this.configuraciones.modulo_ausencias === 'true'
		},
	},
}
</script>
