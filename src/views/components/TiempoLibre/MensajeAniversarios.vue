<template id="content">
	<div>
		<div class="info-vacaciones">
			<h2>🏖️ {{ t('empleados', 'Vacation Table') }}</h2>
			<p>
				{{ t('empleados', 'This table shows how many vacation days you are entitled to based on your years with the company. It is a guide based on the Federal Labor Law, reformed in 2023.') }}
			</p>

			<h3>🤔 {{ t('empleados', 'Frequently Asked Questions') }}</h3>

			<!-- Accordion -->
			<div v-for="(pregunta, index) in preguntas" :key="index" class="acordeon-item">
				<button class="acordeon-titulo" @click="toggle(index)">
					{{ pregunta.titulo }}
					<span>{{ pregunta.abierto ? '➖' : '➕' }}</span>
				</button>
				<div v-show="pregunta.abierto" class="acordeon-contenido">
					<div>{{ pregunta.contenido }}</div>
				</div>
			</div>

			<h3>✅ {{ t('empleados', 'Recommendation') }}</h3>
			<p>
				{{ t('empleados', 'Check this table every time you reach a work anniversary. That way you can plan your time off in advance and enjoy your days to the fullest.') }}
			</p>
		</div>
	</div>
</template>

<script>
import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'MensajeAniversarios',

	props: {
		info: { type: Object, required: true },
		acumular: { type: String, required: true },
	},

	data() {
		return {
			preguntas: [
				{
					titulo: t('empleados', 'From when do I have the right to vacation?'),
					contenido: t('empleados', 'From your first full year worked you can already take vacation. The minimum is 12 days and it increases each year.'),
					abierto: false,
				},
				{
					titulo: t('empleados', 'How are the days counted?'),
					contenido: t('empleados', 'The days shown in the table are business days. Saturdays, Sundays, and holidays are not counted.'),
					abierto: false,
				},
				{
					titulo: t('empleados', 'Can I split my vacation?'),
					contenido: t('empleados', 'Yes. By law, at least half should be taken consecutively, but you can talk to Human Resources to distribute the days according to your needs and your team’s.'),
					abierto: false,
				},
				{
					titulo: t('empleados', 'What happens if I do not take my vacation?'),
					contenido: this.acumular === 'true'
						? t('empleados', 'Unused vacation is not lost, but it is important to use it. Resting is a right and also helps your health and performance.')
						: t('empleados', 'If you do not take your vacation, it is lost. It is important to use it to take care of your health and wellbeing.'),
					abierto: false,
				},
				{
					titulo: t('empleados', 'Can I ask for more vacation days?'),
					contenido: t('empleados', 'Yes, although the legal amount is the minimum, the company may offer more days as an additional benefit. Check your contract or talk to HR.'),
					abierto: false,
				},
			],
		}
	},

	methods: {
		t,
		toggle(index) {
			this.preguntas[index].abierto = !this.preguntas[index].abierto
		},
	},
}
</script>

<style scoped>
.acordeon-item {
	margin-bottom: 10px;
	border-radius: 5px;
	overflow: hidden;
}

.acordeon-titulo {
	width: 100%;
	text-align: left;
	background-color: #f0f0f0;
	border: none;
	padding: 10px;
	font-weight: bold;
	cursor: pointer;
	display: flex;
	justify-content: space-between;
	align-items: center;
	font-size: 16px;
}

.acordeon-contenido {
	padding: 10px;
	background-color: #fafafa;
	border-top: 1px solid #ddd;
	transition: all 0.3s ease-in-out;
}
</style>
