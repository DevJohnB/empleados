<template id="content">
	<PuestosList />
</template>

<script>
import PuestosList from './PuestosList.vue'
import { showError /* showSuccess */ } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'Puestos',
	components: {
		PuestosList,
	},

	data() {
		return {
			loading: true,
		}
	},

	mounted() {
		this.getall()
	},

	methods: {
		t,

		async getall() {
			this.loading = true
			try {
				await axios.get(generateUrl('/apps/empleados/GetPuestosList'))
					.then(
						(response) => {
							// eslint-disable-next-line no-console
							console.log(response)
							this.loading = false
						},
						(err) => {
							this.loading = false
							showError(err)
						},
					)
			} catch (err) {
				this.loading = false
				showError(t('empleados', 'An exception has occurred [01] [{error}]', { error: String(err) }))
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
