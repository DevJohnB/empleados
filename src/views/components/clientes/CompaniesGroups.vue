<template id="content">
	<NcAppContent name="Empleados – Companies & Groups">
		<!-- AdminBoard / -->
		<div class="wrap">
			<NcEmptyContent
				:name="t('empleados', 'Bienvenido al módulo Empleados')"
				:description="t('empleados', 'Administra información y procesos de personal desde un solo lugar.')"
				icon="icon-category-users">
				<template #actions>
					<NcButton type="primary" @click="go('help/guia-rapida')">
						{{ t('empleados', 'Abrir guía rápida') }}
					</NcButton>
					<NcButton @click="go('solicitudes')">
						{{ t('empleados', 'Solicitar acceso / permisos') }}
					</NcButton>
				</template>
			</NcEmptyContent>

			<NcNoteCard type="info" class="mt">
				<strong>{{ t('empleados', 'Privacidad') }}:</strong>
				{{ t('empleados', 'Este panel es público y no muestra datos personales. Para ver información de empleados, necesitas permisos específicos dentro del módulo.') }}
			</NcNoteCard>

			<section class="grid mt">
				<div class="card">
					<h3>{{ t('empleados', '¿Qué puedes hacer aquí?') }}</h3>
					<ul>
						<li>{{ t('empleados', 'Conocer el propósito del módulo y su alcance.') }}</li>
						<li>{{ t('empleados', 'Acceder a documentación básica y contacto de soporte.') }}</li>
						<li>{{ t('empleados', 'Solicitar acceso a secciones restringidas.') }}</li>
					</ul>
				</div>
			</section>
		</div>
	</NcAppContent>
</template>

<script>
// import { showError } from '@nextcloud/dialogs'
// import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
import { NcAppContent, NcEmptyContent, NcButton, NcNoteCard } from '@nextcloud/vue'
// import AdminBoard from './AdminBoard.vue';

// public imports
// i18n: usa t('empleados', '...') si tienes appId = 'empleados'
import { t } from '@nextcloud/l10n'

export default {
	name: 'CompaniesGroups',
	components: { NcAppContent, NcEmptyContent, NcButton, NcNoteCard },
	data() {
		return {
			loading: true,
		}
	},
	async mounted() {
		// Carga inicial de datos si es necesario
	},
	methods: {
		t,
	},
}
</script>

<style scoped lang="scss">
.wrap {
    padding: 16px;
}
</style>
