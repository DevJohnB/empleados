<!-- eslint-disable object-curly-newline -->
<template>
	<div class="contacts-list__item-wrapper">
		<div v-if="Object.keys(data).length === 0">
			<div class="emptycontent">
				<img src="../../../../../img/crowesito-think.png" width="170px">
				<h2>{{ t('empleados', 'Select a team for more details') }}</h2>
			</div>
		</div>

		<div v-else>
			<div class="container-search-profile">
				<div class="button-container-profile">
					<NcActions>
						<template #icon>
							<AccountCog :size="20" />
						</template>

						<NcActionButton :close-after-click="true" @click="showEdit()">
							<template #icon>
								<AccountEdit :size="20" />
							</template>
							{{ t('empleados', 'Enable editing') }}
						</NcActionButton>

						<NcActionButton :close-after-click="true" @click="ChangeView()">
							<template #icon>
								<AccountEdit :size="20" />
							</template>
							{{ t('empleados', 'Change view type') }}
						</NcActionButton>

						<NcActionSeparator />

						<NcActionButton :close-after-click="true" @click="showDialog = true">
							<template #icon>
								<DeleteAlert :size="20" />
							</template>
							{{ t('empleados', 'Delete team') }}
						</NcActionButton>

						<NcDialog
							:open.sync="showDialog"
							:name="t('empleados', 'Confirm')"
							:message="t('empleados', 'Do you want to delete {name}?', { name: data.Nombre })"
							:buttons="buttons" />
					</NcActions>
				</div>
			</div>

			<div class="center">
				<div>
					<h2>{{ data.Nombre }}</h2>
				</div>

				<div class="rsg-title">
					<h3>{{ t('empleados', 'Team') }}</h3>
				</div>

				<div>
					<!-- Cards -->
					<div v-if="preferencias_equipos" class="rsg">
						<ul class="container flex">
							<li v-for="item in peopleArea.equipo" :key="item.Id_empleados" class="flex-item">
								<div class="card">
									<div>
										<NcAvatar :user="item.Id_user" :display-name="item.Id_user" :size="60" />
									</div>
									<div class="right">
										<div class="card-1">
											{{ item.displayname ? item.displayname : item.Id_user }}
										</div>
										<div class="card-2">
											{{ item.Id_user }}
										</div>
									</div>
								</div>
							</li>
						</ul>
					</div>

					<!-- List -->
					<div v-else class="rsgd">
						<ul>
							<NcListItem
								v-for="item in peopleArea.equipo"
								:key="item.Id_empleados"
								bold
								:name="item.displayname ? item.displayname : item.Id_user"
								@click.prevent>
								<template #icon>
									<NcAvatar
										:size="44"
										:user="item.Id_user"
										:display-name="item.displayname ? item.displayname : item.Id_user" />
								</template>
								<template v-if="!item.displayname" #subname>
									{{ item.Id_user }}
								</template>
							</NcListItem>
						</ul>
					</div>
				</div>
			</div>
		</div>

		<!-- Edit modal -->
		<NcModal v-if="show"
			ref="modalRef"
			:name="t('empleados', 'Edit')"
			@close="closeModal">
			<div class="modal__content">
				<div class="container">
					<div class="form-group">
						<NcTextField
							:value.sync="equipo_nombre"
							:v-model="equipo_nombre"
							:label="t('empleados', 'Team name')" />
					</div>

					<div class="form-group">
						<NcSelect
							v-model="selected_user"
							:options="optionsGestor"
							:user-select="true"
							:input-label="t('empleados', 'Team lead')" />
					</div>

					<div class="form-group">
						<NcButton class="center"
							:aria-label="t('empleados', 'Save changes')"
							type="primary"
							@click="GuardarCambioEquipo()">
							{{ t('empleados', 'Save changes') }}
						</NcButton>
					</div>
				</div>
			</div>
		</NcModal>
	</div>
</template>

<script>
// ICONOS
import DeleteAlert from 'vue-material-design-icons/DeleteAlert.vue'
import AccountEdit from 'vue-material-design-icons/AccountEdit.vue'
import AccountCog from 'vue-material-design-icons/AccountCog.vue'

import { generateUrl } from '@nextcloud/router'
import axios from '@nextcloud/axios'
import { showError, showSuccess } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'

import {
	NcAvatar,
	NcActions,
	NcActionButton,
	NcActionSeparator,
	NcDialog,
	NcTextField,
	NcButton,
	NcSelect,
	NcListItem,
	NcModal,
} from '@nextcloud/vue'

export default {
	name: 'EquiposDetails',

	components: {
		NcAvatar,
		NcActionSeparator,
		NcActions,
		AccountCog,
		AccountEdit,
		NcActionButton,
		DeleteAlert,
		NcDialog,
		NcTextField,
		NcButton,
		NcSelect,
		NcListItem,
		NcModal,
	},

	props: {
		data: { type: Object, required: true },
		peopleArea: { type: Object, required: true },
	},

	data() {
		return {
			show: false,
			showDialog: false,

			// Para edición
			equipo_nombre: '',
			optionsGestor: [], // lista de usuarios (from GetConfigurations.Users)
			selected_user: null, // objeto usuario (NcSelect) o string id

			// Preferencias de vista
			preferencias_equipos: null,

			buttons: [
				{
					label: 'Cancelar',
					callback: () => { this.lastResponse = 'Pressed "Cancel"' },
				},
				{
					label: 'Eliminar',
					type: 'primary',
					callback: () => { this.eliminarEquipo(this.data.Id_equipo) },
				},
			],
		}
	},

	mounted() {
		this.$root.$on('show', (data) => { this.show = data })
		this.preferencias_equipos = localStorage.getItem('nextcloud_empleados_preferencias_equipos')
		if (this.preferencias_equipos === null) {
			localStorage.setItem('nextcloud_empleados_preferencias_equipos', 'false')
			this.preferencias_equipos = false
		} else {
			this.preferencias_equipos = this.preferencias_equipos === 'true'
		}
	},

	methods: {
		t,

		async showEdit() {
			this.show = !this.show
			if (this.show) {
				try {
					const response = await axios.get(generateUrl('/apps/empleados/GetConfigurations'))
					this.optionsGestor = response.data.Users
					this.equipo_nombre = this.data.Nombre
					// Si el backend guarda el ID del líder, preseleccionamos:
					// Puede llegar como string (uid) o como objeto, normalizamos:
					const current = this.data.Id_jefe_equipo
					this.selected_user = current || null
				} catch (err) {
					showError(t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
				}
			}
		},

		closeModal() {
			this.show = !this.show
		},

		async eliminarEquipo(equipo) {
			this.showDialog = false
			try {
				await axios.post(generateUrl('/apps/empleados/EliminarEquipo'), { id_equipo: equipo })
				showSuccess(t('empleados', 'Equipo eliminado exitosamente'))
				this.$root.$emit('reload')
				this.$root.$emit('send-data-equipos', {})
			} catch (err) {
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		ChangeView() {
			this.preferencias_equipos = !this.preferencias_equipos
			localStorage.setItem('nextcloud_empleados_preferencias_equipos', this.preferencias_equipos)
		},

		// Normaliza el valor de selected_user para enviar ID correcto
		_normalizeSelectedUser(val) {
			// NcSelect con userSelect suele retornar el objeto de usuario con { id, uid, displayName, ... }
			// pero si ya viene de la API puede ser string. Manejamos ambos.
			if (!val) return ''
			if (typeof val === 'string') return val
			if (typeof val === 'object' && val.id) return val.id
			if (typeof val === 'object' && val.uid) return val.uid
			return String(val)
		},

		async GuardarCambioEquipo() {
			try {
				const idJefe = this._normalizeSelectedUser(this.selected_user)

				await axios.post(generateUrl('/apps/empleados/GuardarCambioEquipo'), {
					Id_Equipo: this.data.Id_equipo,
					Id_jefe_equipo: idJefe,
					Nombre: this.equipo_nombre,
				})

				showSuccess(t('empleados', 'Equipo actualizado exitosamente'))
				this.$root.$emit('reload')
				this.$root.$emit('send-data-equipos', {})
				this.showEdit()
			} catch (err) {
				this.showEdit()
				showError(t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},
	},
}
</script>

<style>
.button-container-profile {
  float: right;
  margin-top: -10px;
}
.container {
  margin-right: 10px;
  margin-left: 10px;
  margin-top: 20px;
  align-items: center;
}
.rsg {
	padding-top: 16px;
	padding-bottom: 16px;
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
	display: flex;
	margin-left: 20px;
	margin-right: 20px;
	width: auto;
	justify-content: center;
}
.rsg-title {
	background-color: rgba(240, 240, 240, 0.37);
	border: 1px solid rgb(232, 232, 232);
	border-radius: 3px;
	margin-left: 20px;
	margin-right: 20px;
	width: auto;
	margin-top: 20px;
}

.item {
  box-shadow: rgba(0, 41, 0, 0.15) 0px 0px 11px 1px;
  width: 100px;
  margin: 10px;
  border-radius: 15px;
}

/*float layout*/
.float { max-width: 1200px; margin: 0 auto; }
.float:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.float-item { float: left; }

/*inline-block*/
.inline-b { max-width:1200px; margin:0 auto; }
.inline-b-item { display: inline-block; }

/*Flexbox*/
.flex {
  padding: 0; margin: 0; list-style: none;
  display: -webkit-box; display: -moz-box; display: -ms-flexbox; display: -webkit-flex; display: flex;
  -webkit-flex-flow: row wrap; justify-content: space-around;
}

h2 { font-size: 28px; margin-bottom: auto; }

.wrapper { display: flex; gap: 4px; align-items: flex-end; flex-wrap: wrap; }

.external-label { display: flex; width: 100%; margin-top: 1rem; }
.external-label label { padding-top: 7px; padding-right: 14px; white-space: nowrap; }

.grid { display: grid; grid-template-columns: repeat(1, 500px); gap: 10px; }

.card {
  --gray: rgba(229, 231, 235, 1);
  width: 350px; display: flex; gap: 1.25rem; border-radius: 1rem;
  background-color: #fff; padding: 1.5rem;
  box-shadow: rgba(0, 41, 0, 0.15) 0px 0px 11px 1px;
}

.card-1 { font-size: 14px; font-weight: bold; }
.right { display: flex; flex: 1 1 0%; flex-direction: column; gap: 1.25rem; }
.card-2 { font-size: 14px; }

@keyframes pulse { to { opacity: .2; } }

.modal__content { margin: 50px; }
.modal__content h2 { text-align: center; }

.form-group {
	margin: calc(var(--default-grid-baseline) * 4) 0;
	display: flex; flex-direction: column; align-items: flex-start;
}
</style>
