<template id="content">
	<NcAppContent name="Empleados – Actividades">
		<div v-if="loading">
			<div class="center">
				<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
			</div>
		</div>

		<div v-else>
			<div class="container">
				<h2 class="board-title">
					<Archive :size="20" decorative class="icon" />
					<span>{{ t('ahorrosgossler', 'Report time') }}</span>
				</h2>
			</div>

			<div class="main-content-card">
				<div class="pack_card">
					<p class="description">
						{{ t('empleados', 'El empleado debe generar el reporte cada día · registrar cada procedimiento por separado · si requiere más registros, usar un nuevo formulario.') }}
					</p>
					<div class="bottom">
						<NcButton
							aria-label="center (default)"
							type="primary"
							wide
							@click="openModal()">
							<template #icon>
								<Check :size="20" />
							</template>
							{{ t('empleados', 'Create report') }}
						</NcButton>
					</div>
				</div>
			</div>

			<div v-if="historial.length > 0">
				<ul style="padding: 10px;">
					<li v-for="item in historial" :key="item.id">
						<NcListItem
							:name="t('ahorrosgossler', 'Movement')"
							:bold="true"
							:active="true"
							:details="formatDate(item.fecha_solicitud)"
							counter-type="highlighted"
							@click.prevent>
							<template #name>
								{{ t('ahorrosgossler', 'Requested amount') }}:
								{{ Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(item.cantidad_solicitada) }}
							</template>

							<template #subname>
								<p v-if="item.nota">
									{{ item.nota }}
								</p>
							</template>

							<template #indicator>
								<!-- estado: 0 = pendiente/rechazado?, !=0 = aprobado (ajústalo a tu lógica si aplica) -->
								<div :title="item.estado == 0 ? t('ahorrosgossler', 'Rejected / pending') : t('ahorrosgossler', 'Approved')">
									<CheckboxBlankCircle :size="16" :fill-color="item.estado == 0 ? '#cc0000' : '#8fce00'" />
								</div>
							</template>
						</NcListItem>
					</li>
				</ul>
			</div>

			<div v-else id="emptycontent">
				<h2>{{ t('ahorrosgossler', 'No movements yet') }}</h2>
			</div>
		</div>

		<NcModal
			v-if="modal"
			ref="modalRef"
			:name="t('empleados', 'Add new activity')"
			@close="closeModal">
			<div class="modal__content">
				<div class="form-group">
					<NcSelect
						v-model="activity_selected"
						:input-label="t('empleados', 'Proyect')"
						:options="actividades"
						class="fit" />
					<div class="time-selector">
						<div class="wrapper">
							<NcDateTimePicker
								v-model="time"
								class="date-picker"
								type="date" />
						</div>
						<div class="estimatetime">
							<NcTextField
								required
								:value.sync="time_activity"
								type="number"
								:label="t('empleados', 'Estimate time')" />
						</div>
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
					</div>
					<NcSelect
						v-model="listas_selected"
						:input-label="t('empleados', 'Activity')"
						:options="listas"
						class="fit" />
					<br>
					<NcTextArea
						required
						resize="vertical"
						:value.sync="description_activity"
						class="top"
						:label="t('empleados', 'Description activity')" />
					<div class="save top">
						<NcButton
							class=""
							:aria-label="t('empleados', 'Create Activity')"
							type="primary"
							@click="create()">
							{{ t('empleados', 'Create Activity') }}
						</NcButton>
					</div>
				</div>
			</div>
		</NcModal>
	</NcAppContent>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import Archive from 'vue-material-design-icons/Archive.vue'
import CheckboxBlankCircle from 'vue-material-design-icons/CheckboxBlankCircle.vue'
import Check from 'vue-material-design-icons/Check.vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import {
	NcLoadingIcon,
	NcListItem,
	NcAppContent,
	NcButton,
	NcTextArea,
	NcCheckboxRadioSwitch,
	NcModal,
	NcTextField,
	NcDateTimePicker,
	NcSelect,
} from '@nextcloud/vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Reports',
	components: {
		NcLoadingIcon,
		NcListItem,
		Archive,
		CheckboxBlankCircle,
		NcAppContent,
		NcButton,
		Check,
		NcTextArea,
		NcCheckboxRadioSwitch,
		NcModal,
		NcTextField,
		NcDateTimePicker,
		NcSelect,
	},
	data() {
		return {
			loading: true,
			historial: [],
			modal: false,
			description_activity: '',
			type_time: 'minutos',
			time_activity: 0,
			time: new Date(),
			listas: [],
			actividades: [],
			activity_selected: null,
			listas_selected: null,
		}
	},
	mounted() {
		this.gethistorial()
	},
	methods: {
		t, // expone t al template

		openModal() {
			this.modal = true
			this.GetActividades()
			this.GetCompaniesGroups()
		},

		closeModal() {
			this.modal = false
		},

		async gethistorial() {
			try {
				const response = await axios.get(generateUrl('apps/empleados/GetReportes'))
				const data = response?.data?.ocs?.data || []
				this.historial = Array.isArray(data) ? data : []
			} catch (e) {
				showError(t('ahorrosgossler', 'Could not fetch your information'))
			} finally {
				this.loading = false
			}
		},

		async GetActividades() {
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
								nombre: 'label',
								tiempo_real: 'count',
							}

							const renameKeys = (obj, map) =>
								Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

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

		async create() {
			try {
				await axios.post(generateUrl('/apps/empleados/crearReporte'), {
					id_cliente: this.listas_selected.id,
					id_actividad: this.activity_selected.id,
					tiemporegistrado: Number(this.time_activity ?? 0),
					descripcion: this.description_activity,
					tipo: this.type_time,
					time: this.time.toISOString().slice(0, 10),
				}).then(
					() => {
						showSuccess(t('empleados', 'Área creada exitosamente'))
						this.GetActividades()
						this.closeModal()
					},
					(err) => { showError(err) },
				)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		formatDate(val) {
			// Si viene ya formateada, la mostramos; si es ISO, la convertimos.
			if (!val) return ''
			// intenta parsear fecha conocida
			const d = new Date(val)
			if (!isNaN(d.getTime())) {
				// Muestra fecha y hora locales (MX)
				return new Intl.DateTimeFormat('es-MX', {
					year: 'numeric',
					month: '2-digit',
					day: '2-digit',
					hour: '2-digit',
					minute: '2-digit',
				}).format(d)
			}
			// si no fue parseable, regresa como viene
			return val
		},
	},
}
</script>

<style scoped>
#emptycontent, .emptycontent { margin-top: 1vh; }
.center-screen {
	display: flex;
	justify-content: center;
	align-items: center;
	text-align: center;
	min-height: 100vh;
}
.center { margin: auto; width: 50%; padding: 10px; }
.container { padding-left: 20px; }
.board-title {
	margin-right: 10px;
	font-size: 25px;
	display: flex;
	align-items: center;
	font-weight: bold;
	margin-left: 20px;
}
.board-title .icon { margin-right: 8px; }
.main-content-card {
	margin-top: 10px;
	margin-left: 20px;
	margin-right: 20px;
}
.time-selector {
	display: flex;
	margin: .5rem 0;          /* margen arriba y abajo */
	align-self: center;
}

.radios {
	display: flex;
	margin-left: 7px;
	height: 35px;
	margin-top: 4px;
}

.estimatetime {
	display: flex;
}

.save {
	display: flex;
	margin-left: 10px;
	align-self: center;
}

.wrapper {
	display: flex;
	flex-direction: column;
}

.type-select {
	display: flex;
	flex-direction: row;
	flex-wrap: wrap;
}
.date-picker {
	margin-top: 3px;
	margin-right: 6px;
}
.fit {
	width: 100%;
}
</style>
