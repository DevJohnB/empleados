<template id="content">
	<NcAppContent name="Empleados – Actividades">
		<List
			:loading="loading"
			:detalles="detalles"
			:select="select" />

		<NcModal
			v-if="addmodal"
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
								class="center"
								:aria-label="t('empleados', 'Create Activity')"
								type="primary"
								@click="sendData()">
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
	</NcAppContent>
</template>

<script>
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
// public imports
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'

import List from '../Helpers/Lists/List.vue'

import {
	NcAppContent,
	NcModal,
	NcTextField,
	NcButton,
	NcTextArea,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'

export default {
	name: 'Actividades',
	components: {
		NcAppContent,
		List,
		NcModal,
		NcTextField,
		NcButton,
		NcTextArea,
		NcCheckboxRadioSwitch,
	},
	data() {
		return {
			loading: true,
			listas: [],
			detalles: [],
			select: [],
			addmodal: false,
			name_activity: '',
			description_activity: '',
			type_time: 'minutos',
			time_activity: 0,
		}
	},

	watch: {
		modal(newVal, oldVal) {
			if (oldVal !== newVal && newVal === true) {
				this.getallsAreas()
			}
		},
	},

	async mounted() {
		this.$root.$on('datagetter', (data) => {
			this.GetActividad(data)
			// this.getalldepartament(data.Id_departamento)
		})
		this.$root.$on('exportlist', () => {
			this.Exportar()
		})
		this.$root.$on('importlist', () => {
			this.$refs.file.click()
		})
		this.$root.$on('newmodal', (data) => {
			this.addmodal = data
		})
		this.getall()
	},
	methods: {
		t,

		closeModal() {
			this.addmodal = false
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

		async getall() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetActividades'))
					.then(
						(response) => {
							if (response?.data?.ocs?.meta?.status !== 'ok') {
								showError(response?.data?.ocs?.meta?.message)
								this.loading = false
								window.location.href = '/apps/empleados/#/'
								return
							}
							const keyMap = {
								id_actividad: 'id',
								nombre: 'name',
								tiempo_real: 'count',
							}

							const renameKeys = (obj, map) =>
								Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

							const arr = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

							this.detalles = arr.map(o => renameKeys(o, keyMap))

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

		async sendData() {
			try {
				await axios.post(generateUrl('/apps/empleados/crearActividad'), {
					nombre: this.name_activity,
					detalles: this.description_activity,
					tiempoestimado: this.time_activity,
					tipo: this.type_time,
				}).then(
					() => {
						showSuccess(t('empleados', 'Área creada exitosamente'))
						this.getall()
						this.closeModal()
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async importar() {
			const formData = new FormData()
			formData.append('AreafileXLSX', this.$refs.file.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/ImportListAreas'), formData, {
					headers: { 'Content-Type': 'multipart/form-data' },
				}).then(
					() => {
						this.$root.$emit('getall')
						this.$root.$emit('reload')
						this.$root.$emit('send-data-areas', [])
						showSuccess(t('empleados', 'Se actualizó la base de datos exitosamente'))
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		Exportar() {
			axios.get(generateUrl('/apps/empleados/ExportListAreas'), { responseType: 'blob' })
				.then(
					(response) => {
						const url = URL.createObjectURL(new Blob([response.data], {
							type: 'application/vnd.ms-excel',
						}))
						const link = document.createElement('a')
						link.href = url
						link.setAttribute('download', 'areas.xlsx')
						document.body.appendChild(link)
						link.click()
					},
					(err) => {
						showError(t('empleados', 'Se ha producido un error {error}, reporte al administrador', { error: String(err) }))
					},
				)
		},

		async getallsAreas() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetAreasFix'))
					.then(
						(response) => { this.options = response?.data?.ocs?.data },
						(err) => { showError(err) },
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
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
</style>
