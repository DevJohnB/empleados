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
				exact>
				<template #icon>
					<ViewDashboard :size="20" />
				</template>
			</NcAppNavigationItem>
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
					:to="{ name: 'Empleados' }">
					<template #icon>
						<BadgeAccountAlert :size="20" />
					</template>
				</NcAppNavigationItem>

				<NcAppNavigationItem
					:name="t('empleados', 'Areas / Departments')"
					:to="{ name: 'Areas' }">
					<template #icon>
						<OfficeBuilding :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem
					:name="t('empleados', 'Positions')"
					:to="{ name: 'Puestos' }">
					<template #icon>
						<AccountTieOutline :size="20" />
					</template>
				</NcAppNavigationItem>
				<NcAppNavigationItem
					:name="t('empleados', 'Teams')"
					:to="{ name: 'Equipos' }">
					<template #icon>
						<AccountGroup :size="20" />
					</template>
				</NcAppNavigationItem>
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
					:to="{ name: 'Ahorros' }">
					<template #icon>
						<FileSign :size="20" />
					</template>
				</NcAppNavigationItem>
			</NcAppNavigationList>
			<NcAppNavigationList v-if="isAdmin()" :aria-labelledby="t('empleados', 'Savings module')">
				<NcAppNavigationItem
					:name="t('empleados', 'Admin panel')"
					:to="{ name: 'PanelAhorros' }">
					<template #icon>
						<Bank :size="20" />
					</template>
				</NcAppNavigationItem>
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
					:to="{ name: 'Calendario' }">
					<template #icon>
						<CalendarBlank :size="20" />
					</template>
				</NcAppNavigationItem>
			</NcAppNavigationList>
		</div>
	</NcAppNavigation>
</template>

<script>
// ICONOS
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import BadgeAccountAlert from 'vue-material-design-icons/BadgeAccountAlert.vue'
import OfficeBuilding from 'vue-material-design-icons/OfficeBuilding.vue'
import AccountTieOutline from 'vue-material-design-icons/AccountTieOutline.vue'
import ViewDashboard from 'vue-material-design-icons/ViewDashboard.vue'
import FileSign from 'vue-material-design-icons/FileSign.vue'
import Bank from 'vue-material-design-icons/Bank.vue'
import CalendarBlank from 'vue-material-design-icons/CalendarBlank.vue'

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
		AccountGroup,
		BadgeAccountAlert,
		OfficeBuilding,
		AccountTieOutline,
		ViewDashboard,
		FileSign,
		Bank,
		CalendarBlank,
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
