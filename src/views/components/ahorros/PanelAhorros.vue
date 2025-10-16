<template id="content">
	<NcAppContent>
		<div v-if="loading">
			<div class="center-screen">
				<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
			</div>
		</div>
		<div v-else>
			<div v-if="historial.length >= 0">
				<div class="container">
					<div style="display: flex; gap: 12px; flex: 1">
						<div>
							<h2 class="board-title">
								<Archive :size="20"
									decorative
									class="icon"
									style="margin-top: 4px;" />
								<span>{{ t('empleados', 'Pending requests') }}</span>
							</h2>
						</div>

						<div style="margin-left: auto; margin-right: 0; margin-top: 10px;">
							<NcSelect v-model="options_estado_values"
								class="container__select"
								:input-label="t('empleados', 'status')"
								:options="options_estado"
								required
								@option:selected="gethistorial()" />
						</div>

						<div>
							<NcSelect v-model="options_fechas_value"
								class="container__select"
								style="margin-top: 10px;"
								:input-label="t('empleados', 'status')"
								:options="options_fechas"
								required
								@option:selected="gethistorial()" />
						</div>

						<div style="margin-top: 10px;">
							<NcButton alignment="end" type="primary" @click="showModal">
								<template #icon>
									<DatabaseExport :size="20" />
								</template>
								{{ t('empleados', 'export') }}
							</NcButton>
						</div>
					</div>
				</div>
				<ul style="padding: 10px;">
					<li v-for="(item, itemIndex) in historial" :key="item.id_historial">
						<NcListItem
							:name="item.displayname"
							:bold="false"
							@click.prevent="showModaldetails(itemIndex)">
							<template #icon>
								<NcAvatar disable-menu
									:size="44"
									:user="item.uid"
									:display-name="item.uid" />
							</template>
							<template #name>
								<span style="display: flex; gap: 0.5rem; color: var(--color-primary);">
									{{ item.displayname }}
								</span>
							</template>
							<template v-if="item.nota" #subname>
								{{ item.nota }}
							</template>
							<template #details>
								${{ item.cantidad_solicitada }}
							</template>
							<template #actions>
								<NcActionButton @click="showModaldetails(itemIndex)">
									<template #icon>
										<Eye :size="20" />
									</template>
									{{ t('empleados', 'View request') }}
								</NcActionButton>
								<NcActionButton v-if="item.estado == 0" @click="accion('aceptar', item.id_historial, item.id_user)">
									{{ t('empleados', 'Approve') }}
								</NcActionButton>
								<NcActionButton v-if="item.estado == 0" @click="accion('denegar', item.id_historial, item.id_user)">
									{{ t('empleados', 'Delete') }}
								</NcActionButton>
							</template>
						</NcListItem>
					</li>
				</ul>
			</div>
			<div v-else id="emptycontent">
				<h2>
					{{ t('empleados', 'No movements have been recorded yet.') }}
				</h2>
			</div>
		</div>

		<NcModal
			v-if="modal"
			ref="modalRef"
			:name="t('empleados', 'Export information')"
			@close="closeModal">
			<div class="center">
				<div v-if="exportardata">
					<div>
						<h2>{{ t('empleados', 'Exporting') }}</h2>
						<form class="center" @submit.prevent>
							<NcProgressBar :value="exportardata_value" size="medium" />
						</form>
					</div>
				</div>
				<div v-else>
					<h2>{{ t('empleados', 'Select the period and request type') }}</h2>
					<br>
					<div>
						<NcSelect v-model="export_estado_values"
							class="container__select"
							:input-label="t('empleados', 'status')"
							:options="options_estado"
							required />
					</div>

					<div>
						<NcSelect v-model="export_fechas_value"
							class="container__select"
							:input-label="t('empleados', 'status')"
							:options="options_fechas"
							required />
					</div>
					<br>
					<NcButton class="center" @click="exportar">
						{{ t('empleados', 'export') }}
					</NcButton>
				</div>
			</div>
		</NcModal>

		<NcModal
			v-if="modaldetails"
			ref="modalRef"
			size="large"
			:name="t('empleados', 'View request')"
			@close="closeModaldetails">
			<div class="center">
				<ul>
					<li>
						<NcAvatar :user="historial[index].uid" :display-name="historial[index].uid" :size="100" />
					</li>
					<li>
						<p>{{ historial[index].displayname }}</p>
					</li>
					<li>
						<p>{{ historial[index]['data'] }}</p>
					</li>
					<li style="margin: 25px">
						<NcNoteCard v-if="historial[index].estado == 0" type="info">
							<p>{{ t('empleados', 'This request has not been answered yet') }}</p>
						</NcNoteCard>
					</li>
					<li>
						<ul>
							<NcListItem
								:name="t('empleados', 'Total Savings:')"
								:compact="true"
								one-line
								@click.prevent>
								<template #subname>
									${{ historial[index].cantidad_total }}
								</template>
							</NcListItem>
							<NcListItem
								:name="t('empleados', 'Requested Savings')"
								:compact="true"
								one-line
								@click.prevent>
								<template #subname>
									${{ historial[index].cantidad_solicitada }}
								</template>
							</NcListItem>
							<NcListItem
								:name="t('empleados', 'Request note')"
								:compact="true"
								one-line
								@click.prevent>
								<template #subname>
									{{ historial[index].nota }}
								</template>
							</NcListItem>
						</ul>
					</li>
					<li>
						<div v-if="historial[index].estado == 0" style="display: flex; flex-direction: column; gap: 12px; margin: 10px;">
							<div style="display: flex; gap: 12px;">
								<div style="display: flex; flex-direction: column; gap: 12px; flex: 1">
									<NcButton type="secondary" wide @click="accion('aceptar', historial[index].id_historial, historial[index].id_user)">
										{{ t('empleados', 'ACCEPT') }}
									</NcButton>
								</div>
								<div style="display: flex; flex-direction: column; gap: 12px; flex: 1">
									<NcButton type="error" wide @click="accion('denegar', historial[index].id_historial, historial[index].id_user)">
										{{ t('empleados', 'DENY') }}
									</NcButton>
								</div>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</NcModal>
	</NcAppContent>
</template>

<script>
import {
	NcAppContent,
	NcActionButton,
	NcLoadingIcon,
	NcButton,
	NcListItem,
	NcAvatar,
	NcModal,
	NcSelect,
	NcProgressBar,
	NcNoteCard,
} from '@nextcloud/vue'

import '@nextcloud/dialogs/styles/toast.scss'
import { generateUrl } from '@nextcloud/router'
import { showError, showSuccess } from '@nextcloud/dialogs'
import axios from '@nextcloud/axios'
import Archive from 'vue-material-design-icons/Archive.vue'
import Eye from 'vue-material-design-icons/Eye.vue'
import DatabaseExport from 'vue-material-design-icons/DatabaseExport.vue'
import { ref } from 'vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'PanelAhorros',
	components: {
		NcAppContent,
		NcActionButton,
		NcLoadingIcon,
		NcButton,
		NcListItem,
		Archive,
		NcAvatar,
		NcModal,
		DatabaseExport,
		NcSelect,
		NcProgressBar,
		NcNoteCard,
		Eye,
	},

	setup() {
		return {
			modalRef: ref(null),
		}
	},

	data() {
		return {
			historial: [],
			loading: true,
			userdata: [],
			userdataahorro: [],
			send: false,

			modal: false,
			modaldetails: false,
			singleValue: null,
			exportardata: false,
			exportardata_value: 0,
			index: 0,
			options_fechas_value: new Date().getFullYear(),
			options_estado_values: t('empleados', 'Pendientes'),
			options_fechas: this.generateYears(),
			options_estado: [t('empleados', 'Pendientes'), t('empleados', 'Aprobados')],

			export_estado_values: t('empleados', 'Aprobados'),
			export_fechas_value: new Date().getFullYear(),
		}
	},

	mounted() {
		this.gethistorial()
	},

	methods: {
		// expone t al template si lo prefieres como método (además del import)
		t,

		async gethistorial() {
			let state
			try {
				state = (this.options_estado_values === t('empleados', 'Pendientes')) ? '0' : '1'
				const response = await axios.get(generateUrl('apps/empleados/GetHistorialPanel/' + this.options_fechas_value + '/' + state))
				this.historial = response?.data?.ocs?.data
				this.loading = false
			} catch (e) {
				console.error(e)
				showError(t('empleados', 'Could not fetch your information'))
			}
		},

		showModal() {
			this.modal = true
		},
		showModaldetails(index) {
			this.index = index
			this.modaldetails = true
		},
		closeModal() {
			this.modal = false
		},
		closeModaldetails() {
			this.modaldetails = false
		},
		exportar() {
			const state = (this.export_estado_values === t('empleados', 'Pendientes')) ? '0' : '1'
			this.exportardata = true
			axios.get(
				generateUrl('/apps/empleados/GenerateReport/' + this.export_fechas_value + '/' + state),
				{ responseType: 'blob' },
			).then(
				(response) => {
					this.exportardata_value = 10
					const url = URL.createObjectURL(new Blob([response.data], { type: 'application/vnd.ms-excel' }))
					const link = document.createElement('a')
					link.href = url
					link.setAttribute('download', 'historial.xlsx')
					document.body.appendChild(link)
					link.click()
					this.exportardata = false
					this.exportardata_value = 0
				},
				(err) => {
					showError(err)
					this.exportardata = false
				},
			)
		},

		generateYears() {
			const currentYear = new Date().getFullYear()
			const startYear = 2023
			const years = []
			for (let year = startYear; year <= currentYear; year++) {
				years.push(year)
			}
			return years
		},

		accion(accion, idahorro, id) {
			this.modaldetails = false
			this.modal = false

			if (accion === 'aceptar') {
				axios.post(
					generateUrl('/apps/empleados/AceptarAhorro'),
					{ id_ahorro: idahorro, id },
				).then(
					async () => {
						this.send = true
						await this.gethistorial()
						showSuccess(t('empleados', 'Solicitud aceptada'))
					},
					(err) => { showError(err) },
				)
			} else {
				axios.post(
					generateUrl('/apps/empleados/DenegarAhorro'),
					{ id_ahorro: idahorro, id },
				).then(
					async () => {
						this.send = true
						await this.gethistorial()
						showSuccess(t('empleados', 'Solicitud Denegada'))
					},
					(err) => { showError(err) },
				)
			}
		},
	},
}
</script>

<style scoped>
	#emptycontent, .emptycontent {
		margin-top: 20vh;
	}
	.center-screen {
		display: flex;
		justify-content: center;
		align-items: center;
		text-align: center;
		min-height: 100vh;
	}
	.center {
		margin: auto;
		padding: 10px;
	}
	.container{
		padding-left: 50px;
        padding-right: 10px;
	}
	.board-title {
		margin-right: 10px;
		margin-top: 14px;
		font-size: 25px;
		display: flex;
		align-items: center;
		font-weight: bold;
		.icon {
			margin-right: 8px;
		}
	}
    .modal__content {
        margin: 50px;
    }

    .modal__content h2 {
        text-align: center;
    }
</style>
