<!-- eslint-disable object-curly-newline -->
<template>
	<div class="contacts-list__item-wrapper">
		<div v-if="Object.keys(data).length == 0">
			<div class="emptycontent">
				<img src="../../../../../img/crowesito-think.png" width="170px">
				<h2>{{ t('empleados', 'Select an area for more details') }}</h2>
			</div>
		</div>
		<div v-else>
			<div>
				<div class="container container-search-profile">
					<div class="button-container-profile">
						<NcActions>
							<template #icon>
								<AccountCog :size="20" />
							</template>
							<NcActionButton
								:close-after-click="true"
								@click="showEdit()">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								{{ t('empleados', 'Enable editing') }}
							</NcActionButton>
							<NcActionButton
								:close-after-click="true"
								@click="ChangeView()">
								<template #icon>
									<AccountEdit :size="20" />
								</template>
								{{ t('empleados', 'Change view type') }}
							</NcActionButton>
							<NcActionSeparator />
							<NcActionButton
								:close-after-click="true"
								@click="showDialog = true">
								<template #icon>
									<DeleteAlert :size="20" />
								</template>
								{{ t('empleados', 'Delete department') }}
							</NcActionButton>
							<NcDialog
								:open.sync="showDialog"
								:name="t('empleados', 'Confirm')"
								:message="t('empleados', 'Do you want to delete {departamento}?', { departamento: data.Nombre })"
								:buttons="buttons" />
						</NcActions>
					</div>
				</div>
			</div>
			<div class="center">
				<div>
					<div>
						<h2>{{ data.Nombre }}</h2>
					</div>
					<div v-if="data.Id_padre">
						<h1>{{ data.Id_padre }}</h1>
					</div>
				</div>
				<div class="rsg-title">
					<h3>{{ t('empleados', 'Employees in department / Area') }}</h3>
				</div>
				<div>
					<div v-if="preferencias_areas" class="rsg">
						<ul class="container flex">
							<li v-for="(item) in peopleArea.area"
								:key="item.Id_empleados"
								class="flex-item">
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
					<div v-else class="rsgd">
						<ul>
							<NcListItem v-for="(item) in peopleArea.area"
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

		<NcModal
			v-if="show"
			ref="modalRef"
			:name="t('empleados', 'Edit')"
			@close="closeModal">
			<div class="modal__content">
				<div class="container">
					<div class="form-group">
						<NcTextField
							:value.sync="area"
							:v-model="area"
							:label="t('empleados', 'Department/area name')" />
					</div>
					<div class="form-group">
						<NcSelect
							v-model="padre"
							:input-label="t('empleados', 'Parent area')"
							:options="options" />
					</div>
					<div class="form-group">
						<NcButton
							class="center"
							:aria-label="t('empleados', 'Save changes')"
							type="primary"
							@click="guardarcambioarea()">
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
	NcSelect,
	NcButton,
	NcListItem,
	NcModal,
} from '@nextcloud/vue'

export default {
	name: 'AreasDetails',

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
		NcSelect,
		NcButton,
		NcListItem,
		NcModal,
	},

	props: {
		data: {
			type: Object,
			required: true,
		},
		peopleArea: {
			type: Object,
			required: true,
		},
	},

	data() {
		return {
			show: false,
			options: [],
			Empleados: [],
			showDialog: false,
			area: '',
			padre: '',
			preferencias_areas: null,
		}
	},

	computed: {
		buttons() {
			return [
				{
					label: this.t('empleados', 'Cancelar'),
					callback: () => { this.lastResponse = 'Pressed "Cancel"' },
				},
				{
					label: this.t('empleados', 'Eliminar'),
					type: 'primary',
					callback: () => { this.eliminarDepartamento(this.data.Id_departamento) },
				},
			]
		},
	},

	mounted() {
		this.$root.$on('show', (data) => {
			this.show = data
		})
		this.preferencias_areas = localStorage.getItem('nextcloud_empleados_preferencias_areas')
		if (this.preferencias_areas === null) {
			localStorage.setItem('nextcloud_empleados_preferencias_areas', 'false')
			this.preferencias_areas = false
		} else {
			this.preferencias_areas = this.preferencias_areas === 'true'
		}
	},

	methods: {
		// expone t en el template
		t,

		showEdit() {
			this.show = !this.show
			if (this.show === true) {
				this.getall()
				this.padre = this.data.Id_padre
				this.area = this.data.Nombre
			}
		},
		closeModal() {
			this.show = !this.show
		},
		async eliminarDepartamento(departamento) {
			this.showDialog = false
			try {
				await axios.post(generateUrl('/apps/empleados/EliminarArea'), {
					id_departamento: departamento,
				})
				showSuccess(this.t('empleados', 'Área eliminada exitosamente'))
				this.$root.$emit('reload')
				this.$root.$emit('send-data-areas', {})
			} catch (err) {
				showError(this.t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
			}
		},

		ChangeView() {
			this.preferencias_areas = !this.preferencias_areas
			localStorage.setItem('nextcloud_empleados_preferencias_areas', this.preferencias_areas)
		},

		checknull(value) {
			return value == null ? '' : value
		},

		async getall() {
			try {
				const response = await axios.get(generateUrl('/apps/empleados/GetAreasFix'))
				this.options = response?.data?.ocs?.data
			} catch (err) {
				showError(this.t('empleados', 'Se ha producido una excepcion [01] [{error}]', { error: String(err) }))
			}
		},

		async guardarcambioarea() {
			if (this.padre == null) {
				this.padre = ''
			} else if (this.padre.label) {
				this.padre = this.padre.label
			}

			try {
				await axios.post(generateUrl('/apps/empleados/GuardarCambioArea'), {
					id_departamento: this.data.Id_departamento,
					padre: this.padre,
					nombre: this.area,
				})
				showSuccess(this.t('empleados', 'Área actualizada exitosamente'))
				this.$root.$emit('reload')
				this.$root.$emit('send-data-areas', {})
				this.showEdit()
			} catch (err) {
				this.showEdit()
				showError(this.t('empleados', 'Se ha producido una excepcion [03] [{error}]', { error: String(err) }))
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
.rsgd {
	padding-top: 16px;
	padding-bottom: 16px;
	border: 1px solid rgb(232, 232, 232);
	margin-left: 20px;
	margin-right: 20px;
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

.float { max-width: 1200px; margin: 0 auto; }
.float:after { content: "."; display: block; height: 0; clear: both; visibility: hidden; }
.float-item { float: left; }

.inline-b { max-width:1200px; margin:0 auto; }
.inline-b-item { display: inline-block; }

.flex {
  padding: 0;
  margin: 0;
  list-style: none;
  display: flex;
  flex-flow: row wrap;
  justify-content: space-around;
}

h2 { font-size: 28px; margin-bottom: auto; }

.wrapper { display: flex; gap: 4px; align-items: flex-end; flex-wrap: wrap; }
.external-label { display: flex; width: 100%; margin-top: 1rem; }
.external-label label { padding-top: 7px; padding-right: 14px; white-space: nowrap; }

.grid { display: grid; grid-template-columns: repeat(1, 500px); gap: 10px; }

.card {
  width: 350px;
  display: flex;
  gap: 1.25rem;
  border-radius: 1rem;
  background-color: #fff;
  padding: 1.5rem;
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
	display: flex;
	flex-direction: column;
	align-items: flex-start;
}
</style>
