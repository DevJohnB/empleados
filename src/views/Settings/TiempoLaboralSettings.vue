<!-- eslint-disable vue/require-v-for-key -->
<template>
	<div>
		<div class="container">
			<div class="flex">
				<div class="mitad">
					<div>
						<div class="table_component" role="region" tabindex="0">
							<table>
								<caption>
									<span class="caption-title">{{ t('empleados', 'Tabla de aniversarios') }}</span>
									<span class="caption-buttons">
										<NcActions>
											<NcActionButton :close-after-click="true" @click="showAddAniversario">
												<template #icon>
													<Plus :size="20" />
												</template>
												{{ t('empleados', 'Crear nuevo aniversario') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="$refs.file.click()">
												<template #icon>
													<Import :size="20" />
												</template>
												{{ t('empleados', 'Importar listado') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="Exportar()">
												<template #icon>
													<Export :size="20" />
												</template>
												{{ t('empleados', 'Exportar listado / plantilla') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="vaciar()">
												<template #icon>
													<Delete :size="20" />
												</template>
												{{ t('empleados', 'Vaciar tabla aniversarios') }}
											</NcActionButton>
										</NcActions>
									</span>
								</caption>
								<thead>
									<tr>
										<th>{{ t('empleados', 'Numero Aniversario') }}</th>
										<th>{{ t('empleados', 'Dias libres') }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item) in Aniversarios" v-bind="$attrs">
										<td>{{ item.numero_aniversario }}</td>
										<td>{{ item.dias }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
				<div class="mitad">
					<div>
						<div class="table_component" role="region" tabindex="0">
							<table>
								<caption>
									<span class="caption-title">{{ t('empleados', 'Tipos de ausencias') }}</span>
									<span class="caption-buttons">
										<NcActions>
											<NcActionButton :close-after-click="true" @click="showAddTipo">
												<template #icon>
													<Plus :size="20" />
												</template>
												{{ t('empleados', 'Crear nuevo tipo de ausencia') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="$refs.fileTipo.click()">
												<template #icon>
													<Import :size="20" />
												</template>
												{{ t('empleados', 'Importar listado') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="ExportarTipo()">
												<template #icon>
													<Export :size="20" />
												</template>
												{{ t('empleados', 'Exportar listado / plantilla') }}
											</NcActionButton>
											<NcActionButton :close-after-click="true" @click="vaciarTipo()">
												<template #icon>
													<Delete :size="20" />
												</template>
												{{ t('empleados', 'Vaciar tabla tipo de ausencias') }}
											</NcActionButton>
										</NcActions>
									</span>
								</caption>
								<thead>
									<tr>
										<th>{{ t('empleados', 'nombre') }}</th>
										<th>{{ t('empleados', 'descripcion') }}</th>
										<th>{{ t('empleados', 'solicitar_archivo') }}</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(item) in TipoAusencias" v-bind="$attrs">
										<td>{{ item.nombre }}</td>
										<td>{{ item.descripcion }}</td>
										<td>{{ item.solicitar_archivo == 1 ? t('empleados', 'sí') : t('empleados', 'no') }}</td>
									</tr>
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>

		<NcModal
			v-if="modalAddAniversario"
			ref="modalRef"
			name="agregar"
			@close="closeModalAniversario">
			<div class="modal__content">
				<h2>{{ t('empleados', 'Agregue informacion del nuevo aniversario') }}</h2>
				<div class="form-group">
					<NcTextField :label="t('empleados', 'numero de aniversario')" :value.sync="NumeroAniversario" />
				</div>
				<div class="form-group">
					<NcTextField :label="t('empleados', 'Dias')" :value.sync="DiasAniversario" />
				</div>

				<NcButton
					:disabled="!NumeroAniversario || !DiasAniversario"
					@click="AgregarNuevoAniversario">
					{{ t('empleados', 'Enviar') }}
				</NcButton>
			</div>
		</NcModal>

		<NcModal
			v-if="modalAddTipo"
			ref="modalRef"
			name="agregar"
			@close="closeModalTipo">
			<div class="modal__content">
				<h2>{{ t('empleados', 'Agregue informacion del nuevo tipo de ausencia') }}</h2>
				<div class="form-group">
					<NcTextField :label="t('empleados', 'nombre')" :value.sync="NombreTipo" />
				</div>
				<div class="form-group">
					<NcTextField :label="t('empleados', 'descripcion')" :value.sync="DescripcionTipo" />
				</div>
				<div class="form-group">
					<NcCheckboxRadioSwitch v-model="SolicitarArchivoTipo">
						{{ t('empleados', 'Solicitar archivo') }}
					</NcCheckboxRadioSwitch>
				</div>

				<NcButton
					:disabled="!NombreTipo || !DescripcionTipo"
					@click="AgregarNuevoTipo">
					{{ t('empleados', 'Enviar') }}
				</NcButton>
			</div>
		</NcModal>

		<input
			ref="file"
			type="file"
			style="display: none"
			accept=".xlsx"
			@change="importar()">
		<input
			ref="fileTipo"
			type="file"
			style="display: none"
			accept=".xlsx"
			@change="importarTipo()">
	</div>
</template>

<script>
// iconos
import Delete from 'vue-material-design-icons/Delete.vue'
import Plus from 'vue-material-design-icons/Plus.vue'
import Import from 'vue-material-design-icons/Import.vue'
import Export from 'vue-material-design-icons/Export.vue'

// imports
import {
	NcActions,
	NcActionButton,
	NcModal,
	NcTextField,
	NcButton,
	NcCheckboxRadioSwitch,
} from '@nextcloud/vue'
import { ref } from 'vue'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'TiempoLaboralSettings',
	components: {
		NcActions,
		NcActionButton,
		NcModal,
		NcTextField,
		NcButton,
		NcCheckboxRadioSwitch,
		Plus,
		Import,
		Export,
		Delete,
	},

	data() {
		return {
			modalAddAniversario: false,
			modalAddTipo: false,
			modalRef: ref(null),

			Aniversarios: [],
			TipoAusencias: [],

			NumeroAniversario: null,
			DiasAniversario: null,

			NombreTipo: null,
			DescripcionTipo: null,
			SolicitarArchivoTipo: false,
		}
	},

	mounted() {
		this.getAniversarios()
		this.getTipo()
	},

	methods: {
		// Exponer t en plantilla
		t,

		showAddAniversario() {
			this.modalAddAniversario = true
		},
		showAddTipo() {
			this.modalAddTipo = true
		},
		closeModalAniversario() {
			this.modalAddAniversario = false
		},
		closeModalTipo() {
			this.modalAddTipo = false
		},

		async getAniversarios() {
			this.closeModalAniversario()
			this.DiasAniversario = null
			this.NumeroAniversario = null
			try {
				await axios.get(generateUrl('/apps/empleados/Getaniversarios'))
					.then(
						(response) => {
							this.Aniversarios = response.data
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async getTipo() {
			this.closeModalTipo()
			this.NombreTipo = null
			this.DescripcionTipo = null
			this.SolicitarArchivoTipo = false
			try {
				await axios.get(generateUrl('/apps/empleados/getTipo'))
					.then(
						(response) => {
							this.TipoAusencias = response.data
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async AgregarNuevoAniversario() {
			try {
				await axios.post(generateUrl('/apps/empleados/AgregarNuevoAniversario'), {
					numero_aniversario: this.NumeroAniversario,
					fecha_de: '',
					fecha_hasta: '',
					dias: this.DiasAniversario,
				})
				showSuccess(t('empleados', 'Nota ha sido actualizada'))
				this.getAniversarios()
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async AgregarNuevoTipo() {
			try {
				await axios.post(generateUrl('/apps/empleados/AgregarNuevoTipo'), {
					nombre: this.NombreTipo,
					descripcion: this.DescripcionTipo,
					solicitar_archivo: this.SolicitarArchivoTipo,
				})
				showSuccess(t('empleados', 'Nota ha sido actualizada'))
				this.getTipo()
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async vaciar() {
			this.closeModalAniversario()
			try {
				await axios.get(generateUrl('/apps/empleados/VaciarAniversarios'))
					.then(
						() => {
							this.getAniversarios()
							showSuccess(t('empleados', 'Tabla de aniversarios vaciada'))
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async vaciarTipo() {
			this.closeModalTipo()
			try {
				await axios.get(generateUrl('/apps/empleados/VaciarTipo'))
					.then(
						() => {
							this.getTipo()
							showSuccess(t('empleados', 'Tabla de aniversarios vaciada'))
						},
						(err) => {
							showError(err)
						},
					)
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		Exportar() {
			axios.get(generateUrl('/apps/empleados/ExportListAniversarios'), { responseType: 'blob' })
				.then(
					(response) => {
						const url = URL.createObjectURL(new Blob([response.data], { type: 'application/vnd.ms-excel' }))
						const link = document.createElement('a')
						link.href = url
						link.setAttribute('download', 'aniversarios.xlsx')
						document.body.appendChild(link)
						link.click()
					},
					(err) => {
						showError(t('empleados', 'Se ha producido un error {error}, reporte al administrador', { error: String(err) }))
					},
				)
		},

		ExportarTipo() {
			axios.get(generateUrl('/apps/empleados/ExportarTipo'), { responseType: 'blob' })
				.then(
					(response) => {
						const url = URL.createObjectURL(new Blob([response.data], { type: 'application/vnd.ms-excel' }))
						const link = document.createElement('a')
						link.href = url
						link.setAttribute('download', 'tipos_ausencias.xlsx')
						document.body.appendChild(link)
						link.click()
					},
					(err) => {
						showError(t('empleados', 'Se ha producido un error {error}, reporte al administrador', { error: String(err) }))
					},
				)
		},

		async importar() {
			const formData = new FormData()
			formData.append('fileXLSX', this.$refs.file.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/ImportListAniversarios'), formData, {
					headers: { 'Content-Type': 'multipart/form-data' },
				})
				this.getAniversarios()
				showSuccess(t('empleados', 'Se actualizo la base de datos exitosamente'))
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		async importarTipo() {
			const formData = new FormData()
			formData.append('fileXLSX', this.$refs.fileTipo.files[0])
			try {
				await axios.post(generateUrl('/apps/empleados/importarTipo'), formData, {
					headers: { 'Content-Type': 'multipart/form-data' },
				})
				this.getTipo()
				showSuccess(t('empleados', 'Se actualizo la base de datos exitosamente'))
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},
	},
}
</script>

<style>
.flex {
    display: flex;
    justify-content: space-between;
}
.mitad {
      flex: 1;
      padding: 20px;
      border: 1px solid #000;
}

.table_component {
    overflow: auto;
    width: 100%;
}

.table_component table {
    border: 1px solid #dededf;
    height: 100%;
    width: 100%;
    table-layout: fixed;
    border-collapse: collapse;
    border-spacing: 1px;
    text-align: left;
}

.table_component th {
    border: 1px solid #dededf;
    background-color: #eceff1;
    color: #000000;
    padding: 5px;
}

.table_component td {
    border: 1px solid #dededf;
    background-color: #ffffff;
    color: #000000;
    padding: 5px;
	white-space: nowrap;
	overflow: hidden;
	text-overflow: ellipsis;
}

.caption-title {
    font-weight: bold;
}

.caption-buttons {
    float: right;
    padding-bottom: 6px;
}

.modal__content {
	margin: 50px;
}

.modal__content h2 {
	text-align: center;
}

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
