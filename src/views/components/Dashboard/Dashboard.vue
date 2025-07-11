<template id="content">
	<NcAppContent name="Loading">
		<div class="container">
			<h2>avisos!</h2>
		</div>
		<div class="center-box">
			<img :src="require('/img/ahorro.jpg')" alt="Logo" class="imagen-responsiva">
		</div>
		<br>
		<div class="center-box-text text-center">
			<div>
				<p>
					El próximo 30 de junio se realizará el depósito del Préstamo del Fondo de Ahorro, el cual no generará intereses. El monto de dicho préstamo podrá ser de hasta el 90% del total ahorrado en este momento, incluyendo tanto la parte patronal como la del trabajador.
				</p>
			</div>
			<br>
			<div>
				<p>
					Ingresa al modulo, <strong>AHORRO GOSSLER</strong>, para consultar el monto que tienes ahorrado y el monto máximo que puedes solicitar como préstamo.
				</p>
			</div>
		</div>
	</NcAppContent>
</template>

<script>
// Importing necessary components

// import { showError /* showSuccess */ } from '@nextcloud/dialogs'
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
import {
	NcAppContent,
} from '@nextcloud/vue'
export default {
	name: 'Dashboard',

	components: {
		NcAppContent,
	},

	data() {
		return {
		}
	},

	mounted() {
	},

	methods: {
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
	.center-box {
		width: 200px;
		margin: 0 auto; /* centra horizontalmente */
		}
	.center-box-text {
		width: 500px;
		margin: 0 auto; /* centra horizontalmente */
		}
	.imagen-responsiva {
		max-width: 600px;
		height: auto;
		display: block;
		}
</style>
