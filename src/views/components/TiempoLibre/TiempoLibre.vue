<template id="content">
	<NcAppContent name="Loading">
		<div class="">
			<div class="text-center section">
				<div v-if="configuraciones.modulo_ausencias_readonly === 'true'">
					<br>
					<NcNoteCard type="error"
						heading="Atención!!!"
						text="El modulo se encuentra en modo solo lectura" />
					<br>
				</div>
				<section class="layout">
					<div class="grow2">
						<div class="text-center sectionPicker">
							<FullCalendar
								ref="fullCalendar"
								:options="calendarOptions"
								class="my-calendar" />
						</div>
					</div>
					<div class="grow1">
						<div class="cards">
							<div class="headers">
								<div class="btn-top-right">
									<NcActions>
										<NcActionButton @click="showAniversarioModal">
											<template #icon>
												<CalendarQuestionOutline :size="20" />
											</template>
											Mi informacion
										</NcActionButton>
									</NcActions>
								</div>
								<div>
									<h2 class="h2-white">
										Vacaciones
									</h2>
								</div>
								<div class="vacations">
									<div class="gl">
										<div v-if="Ausencias.dias_disponibles">
											{{ formatearDias(Ausencias.dias_disponibles) }}
										</div>
										<div v-else>
											<NcLoadingIcon />
										</div>
									</div>
								</div>
							</div>
							<div class="infos">
								<!-- Acordeón notificaciones -->
								<div v-if="notificaciones" class="acordeon-item">
									<button class="acordeon-notification" @click="toggle(0)">
										<div class="noti-wrapper">
											<BellOutline class="bell-icon" :class="{ 'bell-shake': isShaking }" />
											<NcCounterBubble :count="notifications_counter" class="noti-badge" />
										</div>
										<span class="noti-text">Pendientes</span>
										<span class="arrow">{{ accordeon[0].abierto ? '-' : '+' }}</span>
									</button>
									<div :class="['acordeon-contenido', { abierto: accordeon[0].abierto }]">
										<div>
											<div class="rst">
												<div style="max-height: 300px; overflow-y: auto;">
													<ul>
														<NcListItem
															v-for="(item) in notifications_result"
															:key="item.id_historial_ausencias"
															:name="item.displayname ? item.displayname : item.Id_user"
															@click.prevent="employees = []; typePetition = 'employee'; selected_user = item; $refs.fullCalendar.getApi().gotoDate(item.fecha_de); $refs.fullCalendar.getApi().refetchEvents();">
															<template #icon>
																<NcAvatar disable-menu
																	:size="44"
																	:user="item.Id_user"
																	:display-name="item.Id_user" />
															</template>
															<template #subname>
																{{ new Date(item.fecha_de).toLocaleDateString('es-MX', { day: 'numeric', month: 'short', year: 'numeric' }) }}
															</template>
														</NcListItem>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Mostrar solo mis ausencias -->
								<NcButton
									class="btn-top"
									text="center (default)"
									variant="secondary"
									wide
									@click="typePetition = null; $refs.fullCalendar.getApi().refetchEvents()">
									Mostras mis ausencias
								</NcButton>

								<!-- Acordeón vista equipo -->
								<div class="acordeon-item btn-top">
									<button class="acordeon-titulo" @click="toggle(1)">
										Filtrar por equipo
										<span>{{ accordeon[1].abierto ? '-' : '+' }}</span>
									</button>
									<div :class="['acordeon-contenido', { abierto: accordeon[1].abierto }]">
										<div>
											<div class="rst-title">
												<div class="title_flex">
													<div class="subtitle_flex">
														<NcAvatar :user="Equipo.Id_jefe_equipo" :display-name="Equipo.Id_jefe_equipo" :size="20" />
													</div>
													<div class="btn-top-subtitle">
														{{ Equipo.Nombre }}
													</div>
													<div class="flex-to-right">
														<AccountGroup class="pointer" @click="typePetition = 'all'; $refs.fullCalendar.getApi().refetchEvents()" />
													</div>
												</div>
											</div>
											<div class="rst">
												<div style="max-height: 300px; overflow-y: auto;">
													<ul>
														<NcListItem
															v-for="(item) in peopleEquipo.equipo"
															:key="item.Id_empleados"
															:name="item.displayname ? item.displayname : item.Id_user"
															@click.prevent="employees = []; typePetition = 'employee'; selected_user = item; $refs.fullCalendar.getApi().refetchEvents()">
															<template #icon>
																<NcAvatar disable-menu
																	:size="44"
																	:user="item.Id_user"
																	:display-name="item.Id_user" />
															</template>
														</NcListItem>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>

								<!-- Acordeón vista administrativa -->
								<div v-if="isAdmin()" class="acordeon-item btn-top">
									<button class="acordeon-titulo" @click="toggle(2)">
										Administrativo
										<span>{{ accordeon[2].abierto ? '-' : '+' }}</span>
									</button>
									<div :class="['acordeon-contenido', { abierto: accordeon[2].abierto }]">
										<div class="btn-top">
											<NcSelect v-bind="propsEmployees" v-model="employees" />
										</div>
									</div>
								</div>

								<!-- Acordeón mis empleados -->
								<div v-if="subordinates.length > 0" class="acordeon-item">
									<button class="acordeon-titulo" @click="toggle(3)">
										Mis empleados <span>{{ accordeon[3].abierto ? '-' : '+' }}</span>
									</button>
									<div :class="['acordeon-contenido', { abierto: accordeon[3].abierto }]">
										<div>
											<div class="rst-title">
												<div class="title_flex">
													<div class="subtitle_flex">
														Mis empleados
													</div>
													<div class="flex-to-right">
														<AccountGroup class="pointer" @click="typePetition = 'all-employees'; $refs.fullCalendar.getApi().refetchEvents()" />
													</div>
												</div>
											</div>
											<div class="rst">
												<div style="max-height: 300px; overflow-y: auto;">
													<ul>
														<NcListItem
															v-for="(item) in subordinates"
															:key="item.Id_empleados"
															:name="item.displayname ? item.displayname : item.Id_user"
															@click.prevent="employees = []; typePetition = 'employee'; selected_user = item; $refs.fullCalendar.getApi().refetchEvents()">
															<template #icon>
																<NcAvatar disable-menu
																	:size="44"
																	:user="item.Id_user"
																	:display-name="item.Id_user" />
															</template>
														</NcListItem>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="footers">
								<p>
									🔎 {{ vista_actual }}
								</p>
							</div>
						</div>
					</div>
				</section>
			</div>
		</div>
		<!-- INICIO MODAL DETALLES EVENTO -->
		<NcModal
			v-if="modalEvento"
			ref="modalRef"
			size="large"
			name="Detalles ausencia"
			@close="closeModalEvento">
			<div class="modal__content">
				<div class="form-group">
					{{ infoSelected }}
				</div>
			</div>
		</NcModal>
		<!-- FINAL MODAL DETALLES EVENTO -->

		<!-- INICIO MODAL SOLICITUD DE VACACIONES -->
		<NcModal
			v-if="modal"
			size="large"
			name="Formato de ausencias"
			@close="closeModal">
			<NuevaSolicitud
				v-if="modal"
				ref="modalRef"
				:date="date"
				:dias-solicitados="diasSolicitados"
				:dias-disponibles="Ausencias.dias_disponibles"
				:prima="Ausencias.prima_vacacional"
				:employees="propsEmployees.options"
				:admin="isAdmin()"
				@close="closeModal" />
		</NcModal>
		<!-- FINAL MODAL SOLICITUD DE VACACIONES -->

		<!-- INICIO MODAL ANIVERSARIOS SOLO INFO -->
		<NcModal
			v-if="ModalAniversario"
			ref="modalRef"
			size="large"
			name="Tabla de aniversarios"
			@close="closeModalAniversario">
			<div class="table_component" role="region" tabindex="0">
				<div class="modal__content">
					<div class="layout">
						<div class="grow3">
							<TrofeosAniversarios :info="Ausencias" :acumular="configuraciones.acumular_vacaciones" />
							<br>
							<table>
								<caption>
									<span class="caption-title">Tabla de aniversarios</span>
								</caption>
								<thead>
									<tr>
										<th>Aniversario(s)</th>
										<th>Días libres</th>
									</tr>
								</thead>
								<tbody>
									<tr v-for="(grupo, index) in AniversariosAgrupados" :key="index">
										<td>
											<span v-if="grupo.desde === grupo.hasta">
												{{ grupo.desde }}
											</span>
											<span v-else>
												{{ grupo.desde }} al {{ grupo.hasta }}
											</span>
										</td>
										<td>{{ grupo.dias }}</td>
									</tr>
								</tbody>
							</table>
						</div>
						<div class="grow4">
							<MensajeAniversarios :info="Ausencias" :acumular="configuraciones.acumular_vacaciones" />
						</div>
					</div>
				</div>
			</div>
		</NcModal>
		<!-- FINAL MODAL ANIVERSARIOS SOLO INFO -->
	</NcAppContent>
</template>

<script>
// Importing necessary components
import MensajeAniversarios from './MensajeAniversarios.vue'
import TrofeosAniversarios from './TrofeosAniversarios.vue'
import NuevaSolicitud from './Modal/NuevaSolicitud.vue'

import FullCalendar from '@fullcalendar/vue'
import dayGridPlugin from '@fullcalendar/daygrid'
import interactionPlugin from '@fullcalendar/interaction'
import multiMonthPlugin from '@fullcalendar/multimonth'

import { ref } from 'vue'

import usernameToColor from '@nextcloud/vue/functions/usernameToColor'
import { showError, /* showSuccess */ showInfo } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'

// icons
import BellOutline from 'vue-material-design-icons/BellOutline.vue'
import AccountGroup from 'vue-material-design-icons/AccountGroup.vue'
import CalendarQuestionOutline from 'vue-material-design-icons/CalendarQuestionOutline.vue'

import {
	NcAppContent,
	NcModal,
	NcActions,
	NcActionButton,
	NcListItem,
	NcAvatar,
	NcButton,
	NcSelect,
	NcCounterBubble,
	NcLoadingIcon,
	NcNoteCard,
} from '@nextcloud/vue'
export default {
	name: 'TiempoLibre',

	components: {
		MensajeAniversarios,
		TrofeosAniversarios,
		NuevaSolicitud,
		NcAppContent,
		NcModal,
		NcActions,
		NcActionButton,
		AccountGroup,
		CalendarQuestionOutline,
		FullCalendar,
		NcListItem,
		NcAvatar,
		NcButton,
		NcSelect,
		NcCounterBubble,
		BellOutline,
		NcLoadingIcon,
		NcNoteCard,
	},

	inject: ['employee', 'configuraciones', 'groupuser', 'subordinates'],

	data() {
		return {
			modalRef: ref(null),
			attributes: [],
			FechaInitial: null,
			FechaMaxima: null,
			modal: false,
			modalEvento: false,
			ModalAniversario: false,
			Ausencias: [],
			Aniversarios: [],
			diasSolicitados: 0,
			date: ref({
				start: new Date(),
				end: null,
			}),
			range: null,
			calendarOptions: {
				headerToolbar: {
					left: 'prev,next today',
					center: 'title',
					right: 'dayGridMonth,multiMonthYear',
				},
				dayMaxEventRows: true,
				views: {
					multiMonthYear: { type: 'multiMonth', duration: { months: 12 }, buttonText: 'Año' },
					timeGrid: {
						dayMaxEventRows: 5,
					},
				},
				plugins: [dayGridPlugin, interactionPlugin, multiMonthPlugin],
				initialView: 'dayGridMonth',
				locale: 'es',
				events: this.fetchEvents, // esto es suficiente
				dateClick: this.onDateClick,
				eventClick: this.OnClickEvent,
				select: this.onDateRangeSelect,
				selectable: true,
				eventContent(arg) {
					const nombreEmpleado = arg.event.extendedProps.nombre_empleado || 'Empleado Desconocido'
					const imgUrl = `/avatar/${nombreEmpleado}/64` // Ejemplo dinámico

					// eslint-disable-next-line no-console
					console.log('eventContent', arg.event)

					return {
						html: `
							<div style="display: flex; align-items: center;">
								<img src="${imgUrl}" style="width: 16px; height: 16px; border-radius: 50%; margin-right: 4px;">
								<span>${arg.event.title}</span>
							</div>
						`,
					}
				},

			},
			peopleEquipo: {},
			Equipo: {},
			typePetition: null,
			selected_user: null, // Para almacenar el usuario seleccionado
			propsEmployees: {
				inputLabel: 'Todos los empleados',
				userSelect: true,
				multiple: true,
				closeOnSelect: false,
				options: [], // Se llena con todos los usuarios (optionsGestor)
			},
			employees: [], // Para almacenar los empleados seleccionados
			infoSelected: null, // Para almacenar la información del empleado seleccionado
			vista_actual: 'Mis ausencias',
			accordeon: [
				{ abierto: false },
				{ abierto: false },
				{ abierto: false },
				{ abierto: false },
			], // Acordeón para preguntas frecuentes
			notificaciones: false, // Para mostrar de notificaciones
			notifications_counter: 0, // Contador de notificaciones
			loading: false, // Para mostrar el loading
			notifications_result: [], // Para almacenar las notificaciones
			isShaking: false, // Para animar el icono de notificaciones
		}
	},

	computed: {
		AniversariosAgrupados() {
			const agrupados = []
			let inicio = null
			let fin = null
			let diasActual = null

			this.Aniversarios.forEach((item, index) => {
				const diasNumero = Number(item.dias) // Por si viene como string
				if (diasActual === null) {
					inicio = item.numero_aniversario
					fin = item.numero_aniversario
					diasActual = diasNumero
				} else if (diasNumero === diasActual) {
					fin = item.numero_aniversario
				} else {
					agrupados.push({
						desde: inicio,
						hasta: fin,
						dias: diasActual,
					})
					inicio = item.numero_aniversario
					fin = item.numero_aniversario
					diasActual = diasNumero
				}

				if (index === this.Aniversarios.length - 1) {
					agrupados.push({
						desde: inicio,
						hasta: fin,
						dias: diasActual,
					})
				}
			})

			return agrupados
		},
	},

	watch: {
		employees(news) {
			if (news !== null && news.length > 0) {
				this.selected_user = news
				this.typePetition = 'employee'
				this.$refs.fullCalendar.getApi().refetchEvents()
			}
		},
	},

	mounted() {
		this.$bus.on('close-solicitud', () => {
			this.GetAusencias()
			this.closeModal()
			this.$refs.fullCalendar.getApi().refetchEvents()
		})
		this.GetAusencias()
		if (this.isAdmin()) {
			this.updateList()
		}
		this.getEquipos()
		this.GetAllEquipo()
		this.checkNotifications()
	},
	methods: {
		// Verificar si el empleado actual cuenta con subordinados
		async checkNotifications() {
			if (this.subordinates.length > 0) {
				try {
					await axios.get(generateUrl('/apps/empleados/GetNotificationsSubordinates'))
						.then(
							(response) => {
								// eslint-disable-next-line no-console
								console.log('🔔 Notificaciones de subordinados:', response.data)
								if (response.data.length > 0) {
									this.notificaciones = true
									this.notifications_counter = response.data.length // Actualizar el contador de notificaciones
									this.notifications_result = response.data // Guardar las notificaciones para usarlas en el acordeón
									this.startShaking()
								} else {
									this.notificaciones = false
								}
							},
						)
				} catch (err) {
					showError(t('empleados', 'Se ha producido una excepción [01] [' + err + ']'))
				}
			}
		},

		startShaking() {
			setInterval(() => {
				this.isShaking = true
				setTimeout(() => {
					this.isShaking = false
				}, 900) // Duración de la animación (igual que en CSS)
			}, 2000) // Cada 2 segundos
		},

		// Alterna el estado abierto/cerrado de las preguntas frecuentes
		toggle(index) {
			this.accordeon = this.accordeon.map((item, i) => ({
				...item,
				abierto: i === index ? !item.abierto : false,
			}))
		},
		// Abre el modal de aniversarios y carga los datos
		showAniversarioModal() {
			this.getAniversarios()
		},
		// Cierra el modal de solicitud de vacaciones
		closeModal() {
			this.modal = false
		},
		// Cierra el modal de aniversarios
		closeModalAniversario() {
			this.ModalAniversario = false
		},
		closeModalEvento() {
			this.modalEvento = false
		},
		// Obtiene los datos de ausencias del usuario actual
		async GetAusencias() {
			try {
				const response = await axios.post(generateUrl('/apps/empleados/GetAusenciasByUser'), {
					id: this.employee[0].Id_empleados,
				})
				this.Ausencias = response.data[0]
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepción [03] [' + err + ']'))
			}
		},
		// Obtiene la tabla de aniversarios
		async getAniversarios() {
			try {
				const response = await axios.get(generateUrl('/apps/empleados/Getaniversarios'))
				this.Aniversarios = response.data
				this.ModalAniversario = true
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepción [01] [' + err + ']'))
			}
		},
		// Formatea los días disponibles para mostrar texto amigable
		formatearDias(dias) {
			const entero = Math.floor(dias)
			const decimal = dias % 1
			if (decimal === 0) {
				return `${entero} ${entero === 1 ? 'día' : 'días'}`
			} else if (decimal === 0.5) {
				return `${entero} ${entero === 1 ? 'día' : 'días'} y medio`
			} else {
				return `${dias} días`
			}
		},
		// Refresca los eventos del calendario al cambiar de mes/vista
		onDatesSet() {
			this.$refs.fullCalendar.getApi().refetchEvents()
		},
		// Carga los eventos de ausencias para el calendario
		fetchEvents(fetchInfo, success, failure) {
			switch (this.typePetition) {
			case 'all':
				this.getAllAusencias(fetchInfo, success, failure)
				this.vista_actual = 'Todo mi equipo'
				break
			case 'employee':
				this.getEmployeeAusencias(fetchInfo, success, failure)
				this.vista_actual = 'Empleado seleccionado'
				break
			case 'all-employees':
				this.GetAusenciasMyWorkers(fetchInfo, success, failure)
				this.vista_actual = 'Todos mis empleados'
				break
			default:
				this.accordeon = this.accordeon.map(item => ({
					...item,
					abierto: false,
				}))
				this.employees = []
				this.getMyAusencias(fetchInfo, success, failure)
				this.vista_actual = 'Mis ausencias'
			}
		},

		getMyAusencias(fetchInfo, success, failure) {
			// eslint-disable-next-line no-console
			console.log('🔄 Llamando fetchEvents para:', fetchInfo)

			axios.post(generateUrl('/apps/empleados/GetAusenciasHistorial'), {
				desde: fetchInfo.startStr,
				hasta: fetchInfo.endStr,
			})
				.then(r => {
					const data = r.data.message || []
					// eslint-disable-next-line no-console
					console.log('📦 Datos recibidos:', data)

					const events = data.map(item => {
						const fechaInicio = new Date(item.fecha_de)
						const fechaHasta = new Date(item.fecha_hasta)
						fechaHasta.setDate(fechaHasta.getDate() + 1)

						return {
							id: item.id_historial_ausencias,
							title: item.tipo_nombre,
							start: fechaInicio.toISOString(),
							end: fechaHasta.toISOString(),
							allDay: true,
							color: this.color(this.employee[0].Id_user), // Asignar color basado en el usuario
							nombre_empleado: item.nombre_empleado, // Agregar nombre del empleado
						}
					})

					// eslint-disable-next-line no-console
					console.log('✅ Eventos generados:', events)
					success(events)
				})
				.catch(error => {
					console.error('❌ Error al obtener eventos:', error)
					failure(error)
				})
		},

		GetAusenciasMyWorkers(fetchInfo, success, failure) {
			// eslint-disable-next-line no-console
			console.log('🔄 Llamando fetchEvents para:', fetchInfo)

			axios.post(generateUrl('/apps/empleados/GetAusenciasMyWorkers'), {
				desde: fetchInfo.startStr,
				hasta: fetchInfo.endStr,
			})
				.then(r => {
					const data = r.data.message || []
					// eslint-disable-next-line no-console
					console.log('📦 Datos recibidos:', data)

					const events = data.map(item => {
						const fechaInicio = new Date(item.fecha_de)
						const fechaHasta = new Date(item.fecha_hasta)
						fechaHasta.setDate(fechaHasta.getDate() + 1)

						return {
							id: item.id_historial_ausencias,
							title: item.nombre_empleado + ' - ' + item.tipo_nombre,
							start: fechaInicio.toISOString(),
							end: fechaHasta.toISOString(),
							allDay: true,
							color: this.color(item.nombre_empleado), // Asignar color basado en el usuario
							nombre_empleado: item.nombre_empleado, // Agregar nombre del empleado
						}
					})

					// eslint-disable-next-line no-console
					console.log('✅ Eventos generados:', events)
					success(events)
				})
				.catch(error => {
					console.error('❌ Error al obtener eventos:', error)
					failure(error)
				})
		},

		getAllAusencias(fetchInfo, success, failure) {
			// eslint-disable-next-line no-console
			console.log('🔄 Llamando fetchEvents para:', fetchInfo)

			axios.post(generateUrl('/apps/empleados/GetAusenciasHistorialAll'), {
				desde: fetchInfo.startStr,
				hasta: fetchInfo.endStr,
			})
				.then(r => {
					const data = r.data.message || []
					// eslint-disable-next-line no-console
					console.log('📦 Datos recibidos:', data)

					const events = data.map(item => {
						const fechaInicio = new Date(item.fecha_de)
						const fechaHasta = new Date(item.fecha_hasta)
						fechaHasta.setDate(fechaHasta.getDate() + 1)

						return {
							id: item.id_historial_ausencias,
							title: item.nombre_empleado + ' - ' + item.tipo_nombre,
							start: fechaInicio.toISOString(),
							end: fechaHasta.toISOString(),
							allDay: true,
							color: this.color(item.nombre_empleado), // Asignar color basado en el usuario
							nombre_empleado: item.nombre_empleado, // Agregar nombre del empleado
						}
					})

					// eslint-disable-next-line no-console
					console.log('✅ Eventos generados:', events)
					success(events)
				})
				.catch(error => {
					console.error('❌ Error al obtener eventos:', error)
					failure(error)
				})
		},

		getEmployeeAusencias(fetchInfo, success, failure) {
			// eslint-disable-next-line no-console
			console.log('🔄 Llamando fetchEvents para:', fetchInfo)

			axios.post(generateUrl('/apps/empleados/GetAusenciasEmployeeHistorial'), {
				id_employee: this.selected_user, // puede ser objeto o array
				desde: fetchInfo.startStr,
				hasta: fetchInfo.endStr,
			})
				.then(r => {
					const data = r.data.message || []

					const events = data.map(item => {
						const fechaInicio = new Date(item.fecha_de)
						const fechaHasta = new Date(item.fecha_hasta)
						fechaHasta.setDate(fechaHasta.getDate() + 1) // para que sea exclusivo

						return {
							id: item.id_historial_ausencias,
							title: `${item.nombre_empleado} - ${item.tipo_nombre}`,
							start: fechaInicio.toISOString(),
							end: fechaHasta.toISOString(),
							allDay: true,
							color: this.color?.(item.nombre_empleado) || '#3a87ad',
							nombre_empleado: item.nombre_empleado, // Agregar nombre del empleado
						}
					})

					success(events)
				})
				.catch(error => {
					console.error('❌ Error al obtener eventos:', error)
					this.selected_user = null
					failure(error)
				})
		},

		// Acción al hacer clic en una fecha (puedes implementar si se requiere)
		onDateClick(arg) {
			// eslint-disable-next-line no-console
			console.log('Fecha clickeada 1:', arg.dateStr)
			if (this.configuraciones.modulo_ausencias_readonly === 'true') {
				showInfo('Este modulo se encuentra en modo solo lectura')
			}
		},

		OnClickEvent(info) {
			// eslint-disable-next-line no-console
			console.log('Fecha clickeada 2:', info.event)
			this.infoSelected = info.event
			this.modalEvento = true
		},

		color(username) {
			const { r, g, b } = usernameToColor(username)
			return `rgb(${r}, ${g}, ${b})`
		},

		isAdmin() {
			return 'admin' in this.groupuser || 'recursos_humanos' in this.groupuser
		},

		async updateList() {
			try {
				const response = await axios.get(generateUrl('/apps/empleados/GetEmpleadosList'))
				const empleados = response.data.Empleados || []

				// eslint-disable-next-line no-console
				console.log('📦 Lista de usuarios actualizada:', empleados)

				this.propsEmployees.options = empleados.map(user => ({
					Id_empleados: user.Id_empleados, // <- importante: NcSelect usa el UID
					displayName: user.Nombre || user.Id_user, // usa el nombre si existe
					isNoUser: false,
					icon: '',
					user: user.Id_user,
					preloadedUserStatus: {
						icon: '',
						status: user.Estatus === 'activo' ? 'online' : 'offline',
						message: user.Estatus === 'activo' ? 'Activo' : 'Inactivo',
					},
				}))
			} catch (err) {
				showError('Se ha producido una excepción: ' + err)
				console.error(err)
			}
		},

		// Maneja la selección de un rango de fechas en el calendario
		onDateRangeSelect(selection) {
			if (this.configuraciones.modulo_ausencias_readonly === 'true') {
				return
			}
			const nDate = new Date()
			this.range = selection
			if (!this.range || !this.range.start || !this.range.end) return

			const startDate = new Date(this.range.start)
			const endDate = new Date(this.range.end)
			endDate.setDate(endDate.getDate() - 1)

			// No permitir fechas pasadas
			if (!this.isAdmin()) {
				if (
					new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate())
					< new Date(nDate.getFullYear(), nDate.getMonth(), nDate.getDate())
				) {
					showError('No puedes solicitar ausencias en fechas pasadas')
					return
				}
			}

			// No permitir iniciar o terminar en fin de semana
			const dia = endDate.getDay()
			const diaInicio = startDate.getDay()
			if (dia === 0 || dia === 6 || diaInicio === 0 || diaInicio === 6) {
				showError('No puedes iniciar o finalizar tu ausencia en fin de semana')
				return
			}

			// Contar días hábiles
			let fecha = new Date(startDate)
			let diasHabiles = 0
			while (fecha <= endDate) {
				const diaSemana = fecha.getDay()
				if (diaSemana !== 0 && diaSemana !== 6) {
					diasHabiles++
				}
				fecha = new Date(fecha.getFullYear(), fecha.getMonth(), fecha.getDate() + 1) // ✅ AVANZAMOS la fecha
			}

			this.diasSolicitados = diasHabiles
			this.date = {
				start: startDate,
				end: endDate,
			}
			this.modal = true
		},
		async GetAllEquipo() {
			try {
				await axios.get(generateUrl('/apps/empleados/GetMyEquipo'))
					.then(
						(response) => {
							this.peopleEquipo = response.data
						},
					)
			} catch (err) {
				// eslint-disable-next-line no-console
				console.log(err)
			}
		},
		async getEquipos() {
			axios.post(generateUrl(generateUrl('/apps/empleados/GetEquipoJefe')), {
				id: this.employee[0].Id_equipo,
			})
				.then(r => {
					const data = r.data.message || []
					// eslint-disable-next-line no-console
					console.log('📦 Datos recibidos:', data)
					this.Equipo = r.data[0]
				})
				.catch(error => {
					console.error('❌ Error al jefe de equipo:', error)
				})
		},
	},
}
</script>
<style scoped>
.layout {
  width: 100%;
  display: flex;
  gap: 16px;
}

.grow1 { flex: 3; }
.grow2 { flex: 7; }
.grow3 { flex: 3; }
.grow4 { flex: 3; }

.cards {
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  border-radius: 0.75rem;
  background-color: white;
  border: 1px solid #cbd5e0;
}

.headers {
  position: relative;
  background-clip: border-box;
  margin-top: 1.5rem;
  margin-left: 1rem;
  margin-right: 1rem;
  border-radius: 0.75rem;
  background-color: rgb(33 150 243);
  box-shadow: 0 10px 15px -3px rgba(33,150,243,.4), 0 4px 6px -4px rgba(33,150,243,.4);
  height: 8rem;
  text-align: center;
}

.infos {
  border: none;
  padding: 1.5rem;
  text-align: center;
}

.titles {
  color: rgb(38 50 56);
  font-weight: 600;
  font-size: 1.25rem;
  margin-bottom: 0.5rem;
}

.footers {
  padding: 0.75rem;
  border: 1px solid rgb(236 239 241);
  display: flex;
  align-items: center;
  justify-content: space-between;
  background-color: rgba(0, 140, 255, 0.082);
}

.h2-white {
  color: white;
}

.btn-top-right {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background-color: white;
  border: none;
  border-radius: 50%;
  padding: 0.5rem;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
  cursor: pointer;
  font-size: 1rem;
  transition: transform 0.2s ease;
}
.btn-top {
  margin-top: 10px
}

.btn-top-right:hover {
  transform: scale(1.1);
}

.table_component {
  overflow: auto;
  width: 100%;
}

.table_component table {
  border: 1px solid #dededf;
  width: 100%;
  table-layout: fixed;
  border-collapse: collapse;
  text-align: left;
}

.table_component th,
.table_component td {
  border: 1px solid #dededf;
  padding: 5px;
}

.table_component th {
  background-color: #eceff1;
  color: black;
}

.table_component td {
  background-color: white;
  color: black;
}

.caption-title {
  font-weight: bold;
}

.modal__content {
  margin: 50px;
}

.sectionPicker {
	padding: 2px 10px 0 22px;
}

.my-calendar {
	--color-background-dark: transparent !important;
}
.acordeon-item {
	margin-bottom: 10px;
	border-radius: 5px;
	overflow: hidden;
}

.acordeon-titulo {
	width: 100%;
	text-align: center;
	border: none;
	justify-content: space-between;
	align-items: center;
}

.acordeon-contenido {
	max-height: 0;
	opacity: 0;
	overflow: hidden;
	transition: all 0.3s ease-in-out;
}

.acordeon-contenido.abierto {
	max-height: 500px; /* suficiente para cubrir el contenido */
	opacity: 1;
}
.flex-to-right {
	margin-left: auto;
	margin-right: 5%;
	cursor: pointer;
}
.subtitle_flex{
	margin-left: 4%;
}
.btn-top-subtitle {
	margin-top: 3px;
}
.pointer {
	cursor: pointer;
}

.acordeon-notification {
	width: 100%;
	border: none;
	display: flex;
	align-items: center;
	justify-content: space-between;
	position: relative;
}

.noti-wrapper {
	position: initial;
	width: 24px;
	height: 24px;
	display: flex;
	align-items: center;
	justify-content: center;
}

.noti-badge {
	position: absolute;
	top: -5px;
	right: -5px;
}

.noti-text {
	text-align: left;
}

.arrow {
	font-weight: bold;
	color: #666;
}

@keyframes shake {
  0% { transform: rotate(0deg); }
  15% { transform: rotate(-15deg); }
  30% { transform: rotate(15deg); }
  45% { transform: rotate(-10deg); }
  60% { transform: rotate(10deg); }
  75% { transform: rotate(-5deg); }
  90% { transform: rotate(5deg); }
  100% { transform: rotate(0deg); }
}

.bell-shake {
  animation: shake 0.8s ease;
}

</style>
