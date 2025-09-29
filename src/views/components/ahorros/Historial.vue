<template>
	<div v-if="loading">
		<div class="center">
			<NcLoadingIcon :size="64" appearance="dark" name="Loading on light background" />
		</div>
	</div>

	<div v-else>
		<div class="container">
			<h2 class="board-title">
				<Archive :size="20" decorative class="icon" />
				<span>{{ t('ahorrosgossler', 'History') }}</span>
			</h2>
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
</template>

<script>
import { showError } from '@nextcloud/dialogs'
import Archive from 'vue-material-design-icons/Archive.vue'
import CheckboxBlankCircle from 'vue-material-design-icons/CheckboxBlankCircle.vue'
import axios from '@nextcloud/axios'
import { generateUrl } from '@nextcloud/router'
import { NcLoadingIcon, NcListItem } from '@nextcloud/vue'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Historial',
	components: {
		NcLoadingIcon,
		NcListItem,
		Archive,
		CheckboxBlankCircle,
	},
	props: {
		id: { type: Number, required: true },
	},
	data() {
		return {
			loading: true,
			historial: [],
		}
	},
	mounted() {
		this.gethistorial()
	},
	methods: {
		t, // expone t al template

		async gethistorial() {
			try {
				const { data } = await axios.get(generateUrl('apps/empleados/getHistorial/' + this.id))
				this.historial = Array.isArray(data) ? data : []
			} catch (e) {
				console.error(e)
				showError(t('ahorrosgossler', 'Could not fetch your information'))
			} finally {
				this.loading = false
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
}
.board-title .icon { margin-right: 8px; }
</style>
