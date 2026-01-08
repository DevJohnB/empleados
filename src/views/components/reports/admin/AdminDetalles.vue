<!-- eslint-disable object-curly-newline -->
<template>
	<div class="center">
		<h1>Titulo</h1>
		<div class="details">
			<div class="box1Inside">
				<label for="nombre_activity" class="labeltype">
					<!--Bank :size="20" /-->
					{{ t('empleados', 'Activity Name') }}
				</label>
				<input id="nombre_activity"
					:value="nombre_activity"
					type="text"
					readonly
					class="inputtype">
			</div>
			<br>
			<div class="box1Inside">
				<label for="detalles_activity" class="labeltype">
					<!--Bank :size="20" /-->
					{{ t('empleados', 'Activity Name') }}
				</label>
				<input id="detalles_activity"
					:value="detalles_activity"
					type="text"
					readonly
					class="inputtype">
			</div>
			<br>
			<div class="box1Inside">
				<label for="tiempo_estimado_activity" class="labeltype">
					<!--Bank :size="20" /-->
					{{ t('empleados', 'Activity Name') }}
				</label>
				<input id="tiempo_estimado_activity"
					:value="tiempo_estimado"
					type="text"
					readonly
					class="inputtype">
			</div>
			<br>
			<div class="box1Inside">
				<label for="tiempo_real_activity" class="labeltype">
					<!--Bank :size="20" /-->
					{{ t('empleados', 'Activity Name') }}
				</label>
				<input id="tiempo_real_activity"
					:value="tiempo_real"
					type="text"
					readonly
					class="inputtype">
			</div>
		</div>
	</div>
</template>

<script>

// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
// import { showError, showSuccess } from '@nextcloud/dialogs'
// import { translate as t } from '@nextcloud/l10n'

import {
// NcTextField,
} from '@nextcloud/vue'

export default {
	name: 'AdminDetalles',

	components: {
		// NcTextField,
	},

	props: {
		select: { type: Array, required: true },
	},

	data() {
		return {
			nombre_activity: '',
			detalles_activity: '',
			tiempo_estimado: '',
			tiempo_real: '',
		}
	},

	watch: {
		select: {
			immediate: true,
			deep: true,
			handler(nuevo) {
				this.nombre_activity = nuevo?.[0]?.nombre ?? ''
				this.detalles_activity = nuevo?.[0]?.detalles ?? ''
				this.tiempo_estimado = nuevo?.[0]?.tiempo_estimado ?? ''
				this.tiempo_real = nuevo?.[0]?.tiempo_real ?? ''
			},
		},
	},

	mounted() {
		// console.log(this.data)
	},

	methods: {
		// expone t en el template
		t,
	},
}
</script>

<style>
.details {
	padding-left: 10px;
	padding-right: 10px;
}
.box1Inside { flex: 3; }
</style>
