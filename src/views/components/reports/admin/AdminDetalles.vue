<!-- eslint-disable object-curly-newline -->
<template>
	<div class="center">
		<div class="kpi-grid">
			<div class="kpi-card">
				<div class="kpi-value">
					{{ kpisFmt.horas_reportadas }}
				</div>
				<div class="kpi-label">
					Horas reportadas
				</div>
			</div>

			<div class="kpi-card">
				<div class="kpi-value">
					{{ kpisFmt.costo_total }}
				</div>
				<div class="kpi-label">
					Costo total
				</div>
			</div>

			<div class="kpi-card">
				<div class="kpi-value">
					{{ kpisFmt.proyectos_activos }}
				</div>
				<div class="kpi-label">
					Proyectos (clientes)
				</div>
			</div>

			<div class="kpi-card">
				<div class="kpi-value">
					{{ kpisFmt.actividades }}
				</div>
				<div class="kpi-label">
					Actividades
				</div>
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
		sueldo: { type: Number, required: false, default: 0 },
	},

	data() {
		return {
			horasreportadas: '',
			costototal: '',
			proyectosactivos: '',
			actividades: '',
		}
	},

	computed: {
		kpis() {
			const arr = Array.isArray(this.select) ? this.select : []

			const toNum = (v) => {
				if (v === null || v === undefined) return 0
				// soporta "60.00", "60,00", "$1,234.50"
				const s = String(v).trim().replace(',', '.').replace(/[^\d.-]/g, '')
				const x = Number(s)
				return Number.isFinite(x) ? x : 0
			}

			let minutos = 0
			const proyectos = new Set()
			const actividades = new Set()

			for (const it of arr) {
				minutos += toNum(it?.tiempo_registrado)

				if (it?.id_cliente !== null && it?.id_cliente !== undefined) {
					proyectos.add(String(it.id_cliente))
				}

				if (it?.id_actividad !== null && it?.id_actividad !== undefined) {
					actividades.add(String(it.id_actividad))
				}
			}

			const horas = minutos / 60

			// Sueldo por hora del empleado (ej: "175")
			const sueldoHora = toNum(this.sueldo)
			const costo = horas * sueldoHora

			return {
				horas_reportadas: horas,
				costo_total: costo,
				proyectos_activos: proyectos.size,
				actividades: actividades.size,
			}
		},

		kpisFmt() {
			const num2 = new Intl.NumberFormat('es-MX', { maximumFractionDigits: 2 })
			const int = new Intl.NumberFormat('es-MX')
			const money = new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' })

			return {
				horas_reportadas: num2.format(this.kpis.horas_reportadas || 0),
				costo_total: money.format(this.kpis.costo_total || 0),
				proyectos_activos: int.format(this.kpis.proyectos_activos || 0),
				actividades: int.format(this.kpis.actividades || 0),
			}
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
.flex-center {
	display: flex;
	flex-wrap: wrap;
	gap: 10px;
	justify-content: center;
	align-items: center;
}
:root {
  --primary-color: #f6eee5;
  --secondary-color: #e8d8c7;
  --accent: #c9a892;
  --dark-accent: #8c7a76;
  --text-color: #3c3532;
  --light-text: #6d6661;
  --white: #ffffff;
}

/* Grid */
.kpi-grid {
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 20px;
}

/* Card */
.kpi-card {
  background: var(--white);
  border-radius: 10px;
  padding: 22px 20px;
  text-align: center;
  box-shadow: 0 6px 18px rgba(0,0,0,0.06);
  transition: transform 0.25s ease, box-shadow 0.25s ease;
}

.kpi-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 10px 26px rgba(0,0,0,0.1);
}

/* Number */
.kpi-value {
  font-family: "Cormorant Garamond", serif;
  font-size: 2.4rem;
  font-weight: 600;
  color: #555352;;
  line-height: 1.1;
}

/* Label */
.kpi-label {
  margin-top: 6px;
  font-family: system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
  font-size: 0.75rem;
  letter-spacing: 1.5px;
  text-transform: uppercase;
  color: #555352;;
}

/* Responsive */
@media (max-width: 900px) {
  .kpi-grid {
    grid-template-columns: repeat(2, 1fr);
  }
}

@media (max-width: 480px) {
  .kpi-grid {
    grid-template-columns: 1fr;
  }
}

</style>
