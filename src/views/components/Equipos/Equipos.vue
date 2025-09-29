<template id="content">
	<EquiposList />
</template>

<script>
import EquiposList from './EquiposList.vue'
import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Equipos',
	components: {
		EquiposList,
	},

	data() {
		return {
			loading: true,
		}
	},

	async mounted() {
		this.getall()
	},

	methods: {
		async getall() {
			this.loading = false
			try {
				await axios.get(generateUrl('/apps/empleados/GetEquiposList'))
				// Si necesitas algo del response, úsalo aquí.
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},
	},
}
</script>

<style scoped lang="scss">
	.container {
		padding-left: 60px;
	}
	.board-title {
		padding-left: 60px;
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
</style>
