<template id="content">
	<NcAppContent name="Empleados – Actividades">
		<List
			:loading="loading"
			:listas="listas"
			:select="select"
			:defaultbuttons="false"
			:custom="true">
			<template #custombuttons>
				<div class="button-container">
					<NcActions>
						<template #icon>
							<DatabaseCog :size="20" />
						</template>
						<NcActionButton
							:close-after-click="true"
							@click="reportConfig()">
							<template #icon>
								<AccountMultiplePlusOutline :size="20" />
							</template>
							{{ t('empleados', 'configure period') }}
						</NcActionButton>

						<NcActionButton @click="Exportar()">
							<template #icon>
								<DatabaseExport :size="20" />
							</template>
							{{ t('empleados', 'Export period report') }}
						</NcActionButton>

						<NcActionSeparator />
					</NcActions>
				</div>
			</template>
			<template #custom>
				<div class="periodo-details">
					<h3>Resumen - {{ meses.find(m => m.value === periodo_inicio)?.label }} - {{ meses.find(m => m.value === periodo_fin)?.label }}</h3>
				</div>
			</template>
			<template #details>
				<h3>Periodo - {{ meses.find(m => m.value === periodo_inicio)?.label }} - {{ meses.find(m => m.value === periodo_fin)?.label }}</h3>
				<AdminDetalles :select="select" />
			</template>
		</List>

		<NcModal
			v-if="modal"
			ref="modalRef"
			:name="t('empleados', 'Add new activity')"
			@close="closeModal">
			<div class="modal__content">
				<div class="form-group center">
					<NcTextField
						required
						:value.sync="name_activity"
						:label="t('empleados', 'Activity name')" />
					<NcTextArea
						required
						resize="vertical"
						:value.sync="description_activity"
						:label="t('empleados', 'Description activity')" />
					<div class="time-selector">
						<div class="radios">
							<NcCheckboxRadioSwitch
								v-model="type_time"
								:button-variant="true"
								value="minutos"
								name="Minutos"
								type="radio"
								button-variant-grouped="horizontal">
								Minutos
							</NcCheckboxRadioSwitch>
							<NcCheckboxRadioSwitch
								v-model="type_time"
								:button-variant="true"
								value="horas"
								name="Horas"
								type="radio"
								button-variant-grouped="horizontal">
								Horas
							</NcCheckboxRadioSwitch>
						</div>
						<div class="estimatetime">
							<NcTextField
								required
								:value.sync="time_activity"
								type="number"
								:label="t('empleados', 'Estimate time')" />
						</div>
						<div class="save">
							<NcButton
								v-if="editing"
								class="center"
								:aria-label="t('empleados', 'Edit Activity')"
								type="primary"
								@click="modify()">
								{{ t('empleados', 'Edit Activity') }}
							</NcButton>
							<NcButton
								v-else
								class="center"
								:aria-label="t('empleados', 'Create Activity')"
								type="primary"
								@click="create()">
								{{ t('empleados', 'Create Activity') }}
							</NcButton>
						</div>
					</div>
				</div>
			</div>
		</NcModal>
		<input
			ref="file"
			type="file"
			style="display: none"
			accept=".xlsx"
			@change="importar()">
		<NcModal
			v-if="modalReport"
			ref="modalRef"
			:name="t('empleados', 'Add new activity')"
			size="large"
			@close="closeModal">
			<div class="modal__content">
				<div class="form-group center">
					<input
						ref="trapFocus"
						type="text"
						style="position:absolute;opacity:0;height:0;width:0;pointer-events:none;">
					<div class="report-config">
						<NcSelect
							v-model="periodo_inicio"
							:options="meses"
							label="label"
							:reduce="m => m.value"
							input-label="Mes de inicio"
							class="select-date" />

						<NcSelect
							v-model="periodo_fin"
							:options="meses"
							label="label"
							:reduce="m => m.value"
							input-label="Mes de fin"
							class="select-date" />
						<NcSelect
							v-model="anioSeleccionado"
							:options="anios"
							:reduce="a => a"
							input-label="Año"
							class="select-date" />
					</div>
					<NcButton
						class="appli-report"
						:aria-label="t('empleados', 'apply changes')"
						type="primary"
						@click="ChangeReportConfig()">
						{{ t('empleados', 'Apply changes') }}
					</NcButton>
				</div>
			</div>
		</NcModal>
	</NcAppContent>
</template>

<script>
// Icons
import DatabaseExport from 'vue-material-design-icons/DatabaseExport.vue'
import AccountMultiplePlusOutline from 'vue-material-design-icons/AccountMultiplePlusOutline.vue'
import DatabaseCog from 'vue-material-design-icons/DatabaseCog.vue'

// public imports
import { showError /*, showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'

import List from '../../Helpers/Lists/List.vue'
import AdminDetalles from './AdminDetalles.vue'

import {
	NcAppContent,
	NcModal,
	NcTextField,
	NcButton,
	NcTextArea,
	NcCheckboxRadioSwitch,
	NcActions,
	NcActionButton,
	NcSelect,
} from '@nextcloud/vue'

export default {
	name: 'Adminreports',
	components: {
		NcAppContent,
		List,
		NcModal,
		NcTextField,
		NcButton,
		NcTextArea,
		NcCheckboxRadioSwitch,
		AdminDetalles,
		NcActions,
		NcActionButton,
		DatabaseExport,
		AccountMultiplePlusOutline,
		DatabaseCog,
		NcSelect,
	},
	data() {
		return {
			editing: false,
			loading: true,
			listas: [],
			select: [],
			modal: false,
			name_activity: '',
			description_activity: '',
			type_time: 'minutos',
			time_activity: 0,
			modalReport: false,
			periodo_inicio: null,
			periodo_fin: null,
			anioSeleccionado: null,
			actividades: [],
			meses: [
				{ label: 'Enero', value: 1 },
				{ label: 'Febrero', value: 2 },
				{ label: 'Marzo', value: 3 },
				{ label: 'Abril', value: 4 },
				{ label: 'Mayo', value: 5 },
				{ label: 'Junio', value: 6 },
				{ label: 'Julio', value: 7 },
				{ label: 'Agosto', value: 8 },
				{ label: 'Septiembre', value: 9 },
				{ label: 'Octubre', value: 10 },
				{ label: 'Noviembre', value: 11 },
				{ label: 'Diciembre', value: 12 },
			],
			anios: Array.from({ length: Math.max(0, new Date().getFullYear() - 2026 + 1) }, (_, i) => 2026 + i),
		}
	},

	async mounted() {
		this.periodo_inicio = Number(localStorage.getItem('nextcloud_empleados_mes_inicio')) || null
		this.periodo_fin = Number(localStorage.getItem('nextcloud_empleados_mes_fin')) || null
		this.anioSeleccionado = Number(localStorage.getItem('nextcloud_empleados_anio_seleccionado')) || null

		this._onDetails = (id) => this.gethistorial(id)
		this._onNew = () => this.openModal()
		this._onExport = () => this.Exportar()
		this._onImport = () => this.$refs.file.click()
		// deteccion de esc
		window.addEventListener('keydown', this.onKeyDown)

		this.$root.$on('details', this._onDetails)
		this.$root.$on('new', this._onNew)
		this.$root.$on('delete', this._onDelete)
		this.$root.$on('edit', this._onEdit)
		this.$root.$on('exportlist', this._onExport)
		this.$root.$on('importlist', this._onImport)
		this.GetEmpleadosReports()
		this.GetCompaniesGroups()
		this.GetActividades()
	},

	beforeUnmount() {
		window.removeEventListener('keydown', this.onKeyDown)
	},

	beforeDestroy() {
		this.$root.$off('details', this._onDetails)
		this.$root.$off('new', this._onNew)
		this.$root.$off('delete', this._onDelete)
		this.$root.$off('edit', this._onEdit)
		this.$root.$off('exportlist', this._onExport)
		this.$root.$off('importlist', this._onImport)
	},

	methods: {
		t,

		 onKeyDown(e) {
			if (e.key === 'Escape') this.onEsc()
		},

		onEsc() {
			this.select = []
		},

		openModal() {
			this.editing = false
			this.name_activity = null
			this.description_activity = null
			this.type_time = 'minutos'
			this.time_activity = null

			this.modal = true
		},

		closeModal() {
			this.modal = false
			this.modalReport = false
		},

		reportConfig() {
			this.modalReport = true
		},

		async GetActividades() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetActividades'))
					.then(
						(response) => {
							const keyMap = {
								id_actividad: 'id',
								nombre: 'label',
								tiempo_real: 'count',
							}

							const renameKeys = (obj, map) =>
								Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

							const arr = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

							this.actividades = arr.map(o => renameKeys(o, keyMap))

							this.loading = false
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async GetCompaniesGroups() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetCompaniesGroups'))
					.then(
						(response) => {
							if (response?.data?.ocs?.meta?.status !== 'ok') {
								showError(response?.data?.ocs?.meta?.message)
								this.loading = false
								window.location.href = '/apps/empleados/#/'
								return
							}
							const keyMap = {
								id_cliente: 'id',
								nombre: 'name',
								cliente_padre: 'count',
							}

							const renameKeys = (obj, map) =>
								Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

							const arr = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

							this.temp_listas = arr.map(o => renameKeys(o, keyMap))

							const data = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

							// Lista para tu <List>
							this.temp_listas = data.map(o => ({
								id: o.id_cliente,
								name: o.nombre,
								count: o.child_count,
							}))

							// Opciones para <NcSelect>
							this.actividades = data.map(o => ({
								id: o.id_cliente,
								label: o.nombre,
							}))

							this.loading = false
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async GetEmpleadosReports() {
			try {
				await axios.post(generateUrl('/apps/empleados/GetEmpleadosReports'), {
					periodo_inicio: this.periodo_inicio,
					periodo_fin: this.periodo_fin,
					anio: this.anioSeleccionado,
				}).then(
					(response) => {
						if (response?.data?.ocs?.meta?.status !== 'ok') {
							showError(response?.data?.ocs?.meta?.message)
							this.loading = false
							window.location.href = '/apps/empleados/#/'
							return
						}
						const keyMap = {
							Id_empleados: 'id',
							displayname: 'name',
							Id_user: 'image',
							total_tiempo_registrado: 'count',
						}

						const renameKeys = (obj, map) =>
							Object.fromEntries(
								Object.entries(obj).map(([k, v]) => {
									if (k === 'displayname') {
										return ['name', v ?? obj.Id_user]
									}
									return [map[k] ?? k, v]
								}),
							)

						const arr = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

						this.listas = arr.map(o => renameKeys(o, keyMap))

						this.loading = false
					},
					(err) => {
						showError(err)
					},
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		ChangeReportConfig() {
			localStorage.setItem('nextcloud_empleados_mes_inicio', String(this.periodo_inicio ?? ''))
			localStorage.setItem('nextcloud_empleados_mes_fin', String(this.periodo_fin ?? ''))
			localStorage.setItem('nextcloud_empleados_anio_seleccionado', String(this.anioSeleccionado ?? ''))

			this.closeModal()
			this.GetEmpleadosReports()
		},

		async gethistorial(id) {
			try {
				await axios.post(generateUrl('/apps/empleados/GetReportesById'), {
					id,
				}).then(
					(response) => {
						this.select = response?.data?.ocs?.data
					},
					(err) => {
						showError(err)
					},
				)

			} catch (e) {
				showError(t('ahorrosgossler', 'Could not fetch your information'))
			} finally {
				this.loading = false
			}
		},

		Exportar() {
			axios.get(generateUrl('/apps/empleados/ExportarActividades'), { responseType: 'blob' })
				.then(
					(response) => {
						const url = URL.createObjectURL(new Blob([response.data], {
							type: 'application/vnd.ms-excel',
						}))
						const link = document.createElement('a')
						link.href = url
						link.setAttribute('download', 'actividades.xlsx')
						document.body.appendChild(link)
						link.click()
					},
					(err) => {
						showError(t('empleados', 'Se ha producido un error {error}, reporte al administrador', { error: String(err) }))
					},
				)
		},

	},
}
</script>

<style scoped lang="scss">
.time-selector {
	display: flex;
	margin: .5rem 0;          /* margen arriba y abajo */
	align-self: center;
}

.radios {
	display: flex;
	margin-right: 10px;
}

.estimatetime {
	display: flex;
}

.save {
	display: flex;
	margin-left: 10px;
}
.select-date {
	margin-right: 10px;
}
.report-config {
	display: flex;
	flex-direction: row;
	justify-content: center;
	align-items: center;
}
.appli-report {
	margin-top: 15px;
	align-self: center;
}
.periodo-details {
	margin-bottom: 10px;
	text-align: center;
}
</style>
