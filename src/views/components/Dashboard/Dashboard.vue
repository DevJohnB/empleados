<template id="content">
	<NcAppContent name="Empleados – Dashboard">
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
				<div class="card">
					<h3>{{ t('empleados', 'Recursos') }}</h3>
					<ul class="links">
						<li><a :href="doc('help/guia-rapida')" target="_self">{{ t('empleados', 'Guía rápida') }}</a></li>
						<li><a :href="doc('help/politicas')" target="_self">{{ t('empleados', 'Políticas y buenas prácticas') }}</a></li>
						<li><a :href="doc('help/soporte')" target="_self">{{ t('empleados', 'Soporte / Contacto') }}</a></li>
					</ul>
				</div>
			</section>
		</div>
	</NcAppContent>
</template>

<script>
// import { showError } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
// import axios from '@nextcloud/axios'
import { NcAppContent, NcEmptyContent, NcButton, NcNoteCard } from '@nextcloud/vue'
// import AdminBoard from './AdminBoard.vue';

// public imports
// i18n: usa t('empleados', '...') si tienes appId = 'empleados'
import { t } from '@nextcloud/l10n'

export default {
	name: 'Dashboard',
	components: { NcAppContent, NcEmptyContent, NcButton, NcNoteCard },
	data() {
		return {
			loading: true,
			stats: {
				totalEmpleados: 0,
				totalAreas: 0,
				ausenciasHoy: 0,
				aniversariosMes: 0,
			},
			aniversarios: [],
			ausenciasHoy: [],
			actividad: [],
		}
	},
	async mounted() {
		await this.loadData()
	},
	methods: {
		t,
		go(path) {
			// Ajusta estas rutas a tus controladores/páginas públicas
			window.location.href = generateUrl(`/apps/empleados/${path}`)
		},
		doc(path) {
			// Centraliza URLs de documentación pública del módulo
			return generateUrl(`/apps/empleados/${path}`)
		},
		async loadData() {
			this.loading = true
			try {
				// === ENDPOINTS REALES (cámbialos si ya los tienes) ===
				// const { data: s } = await axios.get(generateUrl('/apps/empleados/api/stats'))
				// const { data: aniv } = await axios.get(generateUrl('/apps/empleados/api/aniversarios?dias=30'))
				// const { data: aus } = await axios.get(generateUrl('/apps/empleados/api/ausencias?fecha=hoy'))
				// const { data: act } = await axios.get(generateUrl('/apps/empleados/api/actividad?limit=10'))

				// === MOCKS (elimínalos cuando tus endpoints estén listos) ===
				const s = {
					totalEmpleados: 128,
					totalAreas: 12,
					ausenciasHoy: 3,
					aniversariosMes: 7,
				}
				const aniv = [
					{ id: 1, nombre: 'María Pérez', area: 'Auditoría', fecha: '2025-10-04', years: 3 },
					{ id: 2, nombre: 'Juan López', area: 'TI', fecha: '2025-10-09', years: 1 },
					{ id: 3, nombre: 'Ana Torres', area: 'Fiscal', fecha: '2025-10-15', years: 5 },
				]
				const aus = [
					{ id: 11, nombre: 'Carlos Díaz', tipo: 'Vacaciones', de: '09:00', hasta: '18:00', area: 'Consultoría' },
					{ id: 12, nombre: 'Luisa Rey', tipo: 'Incapacidad', de: 'Todo el día', hasta: '', area: 'TI' },
					{ id: 13, nombre: 'Hugo N.', tipo: 'Permiso', de: '14:00', hasta: '18:00', area: 'Fiscal' },
				]
				const act = [
					{ id: 101, titulo: 'Actualizado perfil de: María Pérez', usuario: 'luis.alvarado', fecha: '2025-10-02 10:21' },
					{ id: 102, titulo: 'Nueva ausencia: Carlos Díaz', usuario: 'rrhh', fecha: '2025-10-02 09:10' },
					{ id: 103, titulo: 'Alta de empleado: Ana Torres', usuario: 'rrhh', fecha: '2025-10-01 17:45' },
				]

				// Asigna
				this.stats = s
				this.aniversarios = aniv
				this.ausenciasHoy = aus
				this.actividad = act
			} catch (e) {
				// showError(t('empleados', 'No se pudo cargar el dashboard'))
				console.error(e)
			} finally {
				this.loading = false
			}
		},
	},
}
</script>

<style scoped lang="scss">
.dashboard {
	padding: 24px 32px;
	display: grid;
	grid-template-columns: 1fr;
	gap: 20px;
}

/* KPIs */
.kpis {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 12px;
}
.kpi {
	background: var(--color-background-darker);
	border: 1px solid var(--color-border);
	border-radius: 12px;
	padding: 16px;
	display: grid;
	gap: 6px;
}
.kpi-label {
	font-size: 12px;
	color: var(--color-text-maxcontrast);
	text-transform: uppercase;
	letter-spacing: .03em;
}
.kpi-value {
	font-size: 28px;
	font-weight: 700;
}

/* Acciones rápidas */
.quick h3 { margin: 0 0 8px; }
.quick-grid {
	display: grid;
	grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
	gap: 10px;
}
.nc-btn {
	appearance: none;
	border: 1px solid var(--color-border);
	background: var(--color-primary-element);
	color: var(--color-primary-text);
	border-radius: 10px;
	padding: 10px 12px;
	font-weight: 600;
	cursor: pointer;
}
.nc-btn:hover { filter: brightness(1.05); }

/* Paneles */
.panel {
	background: var(--color-background-darker);
	border: 1px solid var(--color-border);
	border-radius: 12px;
	padding: 14px;
}
.panel-head {
	display: flex;
	align-items: center;
	justify-content: space-between;
	margin-bottom: 8px;
}
.nc-link {
	background: transparent;
	border: none;
	color: var(--color-primary);
	cursor: pointer;
	text-decoration: underline;
	padding: 4px 6px;
}
.empty {
	padding: 16px;
	color: var(--color-text-maxcontrast);
}

.list {
	list-style: none;
	margin: 0;
	padding: 0;
}
.item {
	display: flex;
	align-items: center;
	justify-content: space-between;
	padding: 10px 8px;
	border-top: 1px solid var(--color-border);
}
.item:first-child { border-top: none; }

.item-main {
	display: flex;
	align-items: baseline;
	gap: 6px;
}
.item-meta {
	display: flex;
	align-items: center;
	gap: 10px;
}
.muted {
	color: var(--color-text-maxcontrast);
	font-size: 12px;
}
.pill {
	border: 1px solid var(--color-border);
	border-radius: 999px;
	padding: 2px 8px;
	font-size: 12px;
}

@media (min-width: 1100px) {
	.dashboard {
		grid-template-columns: 1fr 1fr;
	}
	/* Haz que KPIs ocupen todo el ancho arriba */
	.kpis {
		grid-column: 1 / -1;
	}
}

.wrap { padding: 24px 32px; }
.mt { margin-top: 16px; }
.grid {
	display: grid;
	grid-template-columns: 1fr;
	gap: 16px;
}
.card {
	background: var(--color-background-darker);
	border: 1px solid var(--color-border);
	border-radius: 12px;
	padding: 16px;
}
.card h3 { margin: 0 0 8px; }
.links { list-style: none; padding: 0; margin: 0; }
.links li a { text-decoration: underline; }
@media (min-width: 900px) {
	.grid { grid-template-columns: 1fr 1fr; }
}
</style>
