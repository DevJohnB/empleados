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
					<h3>Periodo - {{ periodo_inicio }} - {{ periodo_fin }}</h3>
				</div>
			</template>
			<template #details>
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
							input-label="Mes de inicio"
							class="select-date" />
						<NcSelect
							v-model="periodo_fin"
							:options="meses"
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
import { showError, showSuccess } from '@nextcloud/dialogs'
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
			periodo_inicio: '',
			periodo_fin: '',
			anioSeleccionado: null,
			meses: [
				'Enero',
				'Febrero',
				'Marzo',
				'Abril',
				'Mayo',
				'Junio',
				'Julio',
				'Agosto',
				'Septiembre',
				'Octubre',
				'Noviembre',
				'Diciembre',
			],
			anios: Array.from({ length: 10 }, (_, i) => 2024 + i),
		}
	},

	async mounted() {
		this.periodo_inicio = localStorage.getItem('nextcloud_empleados_mes_inicio')
		this.periodo_fin = localStorage.getItem('nextcloud_empleados_mes_fin')
		this.anioSeleccionado = localStorage.getItem('nextcloud_empleados_anio_seleccionado')

		this._onDetails = (id) => this.GetActividad(id)
		this._onNew = () => this.openModal()
		this._onDelete = () => this.delete()
		this._onEdit = () => this.edit()
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

		async GetActividad(id) {
			try {
				await axios.post(generateUrl('/apps/empleados/GetActividad'), {
					id,
				}).then(
					(response) => {
						this.select = response?.data?.ocs?.data
					},
					(err) => { showError(err) },
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
						}

						const renameKeys = (obj, map) =>
							Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

						const arr = Array.isArray(response?.data?.ocs?.data.Empleados) ? response.data.ocs.data.Empleados : []

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

		async create() {
			try {
				await axios.post(generateUrl('/apps/empleados/crearActividad'), {
					nombre: this.name_activity,
					detalles: this.description_activity,
					tiempoestimado: this.time_activity != null ? this.time_activity : 0,
					tipo: this.type_time,
				}).then(
					() => {
						showSuccess(t('empleados', 'Área creada exitosamente'))
						this.GetEmpleadosReports()
						this.closeModal()
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async delete() {
			try {
				await axios.post(generateUrl('/apps/empleados/DeleteActividad'), {
					id: this.select[0].id_actividad,
				}).then(
					() => {
						showSuccess(t('empleados', 'Se ha eliminadon exitosamente'))
						this.GetEmpleadosReports()
						this.closeModal()
						this.select = []
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async modify() {
			try {
				await axios.post(generateUrl('/apps/empleados/ModificarActividad'), {
					id_actividad: this.select[0].id_actividad,
					nombre: this.name_activity,
					detalles: this.description_activity,
					tiempoestimado: this.time_activity,
					tipo: this.type_time,
				}).then(
					() => {
						showSuccess(t('empleados', 'Modificacion exitosa'))
						const id = this.select?.[0]?.id_actividad ?? null
						this.select = [{
							id_actividad: id,
							nombre: this.name_activity,
							detalles: this.description_activity,
							tiempo_estimado: this.time_activity,
							tipo: 'minutos',
						}]
						this.GetEmpleadosReports()
						this.closeModal()
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		edit() {
			this.editing = true
			this.name_activity = this.select[0].nombre
			this.description_activity = this.select[0].detalles
			this.type_time = 'minutos'
			this.time_activity = this.select[0].tiempo_estimado
			this.modal = true
		},

		ChangeReportConfig() {
			localStorage.setItem('nextcloud_empleados_mes_inicio', this.periodo_inicio)
			localStorage.setItem('nextcloud_empleados_mes_fin', this.periodo_fin)
			localStorage.setItem('nextcloud_empleados_anio_seleccionado', this.anioSeleccionado)
			this.closeModal()
			this.GetEmpleadosReports()
		},

		async importar() {
			const formData = new FormData()
			formData.append('ActividadesfileXLSX', this.$refs.file.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/ImportarActividades'), formData, {
					headers: { 'Content-Type': 'multipart/form-data' },
				}).then(
					() => {
						this.GetEmpleadosReports()
						showSuccess(t('empleados', 'Se actualizó la base de datos exitosamente'))
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
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
