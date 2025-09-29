<!-- eslint-disable vue/require-v-for-key -->
<template>
	<div class="container">
		<VueTabs>
			<VTab :title="t('empleados', 'Employees')">
				<EmpleadosSettings />
			</VTab>

			<VTab :title="t('empleados', 'Working time')">
				<TiempoLaboralSettings />
			</VTab>

			<VTab :title="t('empleados', 'Settings')">
				<ListSettings />
			</VTab>
		</VueTabs>
	</div>
</template>

<script>
import TiempoLaboralSettings from './TiempoLaboralSettings.vue'
import EmpleadosSettings from './EmpleadosSettings.vue'
import ListSettings from './ListSettings.vue'

import { VueTabs, VTab } from 'vue-nav-tabs/dist/vue-tabs.js'
import 'vue-nav-tabs/themes/vue-tabs.css'

export default {
	name: 'Settings',
	components: {
		EmpleadosSettings,
		TiempoLaboralSettings,
		ListSettings,
		VueTabs,
		VTab,
	},
}
</script>

<style>
.board-title {
	padding-left: 20px;
	margin-right: 10px;
	margin-top: 14px;
	font-size: 25px;
	display: flex;
	align-items: center;
	font-weight: bold;
}
.board-title .icon {
	margin-right: 8px;
}

.center-screen {
  display: flex;
  justify-content: center;
  align-items: center;
  text-align: center;
  min-height: 100vh;
}

.titles {
	margin-right: 10px;
	margin-top: 14px;
	font-size: 17px;
	display: flex;
	align-items: center;
}
.titles .icon {
	margin-right: 8px;
}

.container {
	padding-left: 20px;
	padding-right: 20px;
	margin-top: 10px;
}

.rsg {
	padding-top: 16px;
	padding-bottom: 16px;
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
	display: flex;
	margin-left: 20px;
	margin-right: 20px;
	width: auto;
}
</style>
