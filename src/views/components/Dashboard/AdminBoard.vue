<!-- eslint-disable object-curly-newline -->
<template>
	<div class="dashboard">
		<!-- KPIs -->
		<section class="kpis">
			<div class="kpi">
				<div class="kpi-label">
					Empleados
				</div>
				<div class="kpi-value">
					{{ loading ? '…' : stats.totalEmpleados }}
				</div>
			</div>
			<div class="kpi">
				<div class="kpi-label">
					Áreas
				</div>
				<div class="kpi-value">
					{{ loading ? '…' : stats.totalAreas }}
				</div>
			</div>
			<div class="kpi">
				<div class="kpi-label">
					Ausencias hoy
				</div>
				<div class="kpi-value">
					{{ loading ? '…' : stats.ausenciasHoy }}
				</div>
			</div>
			<div class="kpi">
				<div class="kpi-label">
					Aniversarios (30 días)
				</div>
				<div class="kpi-value">
					{{ loading ? '…' : stats.aniversariosMes }}
				</div>
			</div>
		</section>

		<!-- Acciones rápidas -->
		<section class="quick">
			<h3>Acciones rápidas</h3>
			<div class="quick-grid">
				<button class="nc-btn" @click="go('empleados')">
					Ver empleados
				</button>
				<button class="nc-btn" @click="go('empleados/nuevo')">
					Alta de empleado
				</button>
				<button class="nc-btn" @click="go('areas')">
					Áreas y puestos
				</button>
				<button class="nc-btn" @click="go('ausencias')">
					Gestionar ausencias
				</button>
				<button class="nc-btn" @click="go('reportes')">
					Reportes
				</button>
				<button class="nc-btn" @click="go('config')">
					Configuración
				</button>
			</div>
		</section>

		<!-- Próximos aniversarios -->
		<section class="panel">
			<div class="panel-head">
				<h3>Próximos aniversarios (30 días)</h3>
				<button class="nc-link" @click="go('aniversarios')">
					Ver todo
				</button>
			</div>
			<div v-if="loading" class="empty">
				Cargando…
			</div>
			<ul v-else-if="aniversarios.length" class="list">
				<li v-for="a in aniversarios" :key="a.id" class="item">
					<div class="item-main">
						<strong>{{ a.nombre }}</strong>
						<span class="muted">· {{ a.area }}</span>
					</div>
					<div class="item-meta">
						<span class="pill">{{ a.fecha }}</span>
						<span class="muted">{{ a.years }} años</span>
					</div>
				</li>
			</ul>
			<div v-else class="empty">
				Sin aniversarios próximos.
			</div>
		</section>

		<!-- Ausencias hoy -->
		<section class="panel">
			<div class="panel-head">
				<h3>Ausencias de hoy</h3>
				<button class="nc-link" @click="go('ausencias')">
					Gestionar
				</button>
			</div>
			<div v-if="loading" class="empty">
				Cargando…
			</div>
			<ul v-else-if="ausenciasHoy.length" class="list">
				<li v-for="x in ausenciasHoy" :key="x.id" class="item">
					<div class="item-main">
						<strong>{{ x.nombre }}</strong>
						<span class="muted">· {{ x.tipo }}</span>
					</div>
					<div class="item-meta">
						<span class="pill">{{ x.de }} → {{ x.hasta }}</span>
						<span class="muted">{{ x.area }}</span>
					</div>
				</li>
			</ul>
			<div v-else class="empty">
				Nadie ausente hoy.
			</div>
		</section>

		<!-- Últimos cambios -->
		<section class="panel">
			<div class="panel-head">
				<h3>Últimos cambios</h3>
				<button class="nc-link" @click="go('actividad')">
					Ver actividad
				</button>
			</div>
			<div v-if="loading" class="empty">
				Cargando…
			</div>
			<ul v-else-if="actividad.length" class="list">
				<li v-for="e in actividad" :key="e.id" class="item">
					<div class="item-main">
						<strong>{{ e.titulo }}</strong>
						<span class="muted">· {{ e.usuario }}</span>
					</div>
					<div class="item-meta">
						<span class="muted">{{ e.fecha }}</span>
					</div>
				</li>
			</ul>
			<div v-else class="empty">
				Sin actividad reciente.
			</div>
		</section>
	</div>
</template>