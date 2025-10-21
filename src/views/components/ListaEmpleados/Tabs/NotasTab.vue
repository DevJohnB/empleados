<template>
	<div class="well">
		<div class="editbutton">
			<NcActions>
				<NcActionButton :close-after-click="true" @click="showEdit">
					<template #icon>
						<LanguageMarkdown :size="20" />
					</template>
					{{ showMarkdown ? t('empleados', 'Disable design') : t('empleados', 'Enable design') }}
				</NcActionButton>
			</NcActions>
		</div>

		<div class="top">
			<div>
				<NcRichText
					v-if="showMarkdown"
					:class="{ 'plain-text': !useMarkdown }"
					:text="inputValue"
					:autolink="true"
					:use-markdown="useMarkdown" />

				<!-- Employee Notes -->
				<NcTextArea
					v-else
					input-class="model"
					class="top"
					:label="t('empleados', 'Employee notes')"
					resize="vertical"
					:disabled="show"
					:value.sync="inputValue" />
			</div>

			<NcButton
				v-if="automaticsave === 'false'"
				:aria-label="t('empleados', 'Save note')"
				type="primary"
				@click="guardarNota">
				{{ t('empleados', 'Save note') }}
			</NcButton>

			<br>
		</div>
	</div>
</template>

<script>
import { showError, showSuccess } from '@nextcloud/dialogs'
import { generateUrl } from '@nextcloud/router'
import 'vue-nav-tabs/themes/vue-tabs.css'
import axios from '@nextcloud/axios'
import debounce from 'debounce'
import { translate as t } from '@nextcloud/l10n' // <-- agrega t

// ICONOS
import LanguageMarkdown from 'vue-material-design-icons/LanguageMarkdown.vue'

import {
	NcTextArea,
	NcButton,
	NcRichText,
	NcActions,
	NcActionButton,
} from '@nextcloud/vue'

export default {
	name: 'NotasTab',

	components: {
		NcTextArea,
		NcButton,
		NcRichText,
		NcActions,
		NcActionButton,
		LanguageMarkdown,
	},

	props: {
		data: {
			type: Object,
			required: true,
		},
		show: {
			type: Boolean,
			required: true,
		},
		empleados: {
			type: Array,
			required: true,
		},
		automaticsave: {
			type: String,
			required: true,
		},
	},

	data() {
		return {
			notas: this.data.Notas ?? '',
			showMarkdown: false,
			useMarkdown: true,
		}
	},

	computed: {
		inputValue: {
			get() {
				return this.notas
			},
			set(value) {
				this.debouncePropertyChange(value.trim())
			},
		},
		debouncePropertyChange() {
			return debounce(function(value) {
				this.notas = value
				if (this.automaticsave === 'true') {
					this.guardarNota()
				}
			}, 900)
		},
	},

	watch: {
		data(news) {
			this.notas = news.Notas
		},
	},

	mounted() {
		this.notas = this.data.Notas
	},

	methods: {
		// expone t al template por si lo necesitas como método
		t,

		showEdit() {
			this.showMarkdown = !this.showMarkdown
		},

		async guardarNota() {
			try {
				this.debouncePropertyChange.flush?.()
				await axios.post(generateUrl('/apps/empleados/GuardarNota'), {
					id_empleados: this.data.Id_empleados,
					nota: this.notas,
				})
				showSuccess(t('empleados', 'Note has been updated'))
				this.$bus?.emit('getall')
			} catch (err) {
				showError(t('empleados', 'An exception has occurred [03] [{error}]', { error: String(err) }))
			}
		},
	},
}
</script>

<style>
.editbutton{
	float: right;
	position: absolute;
	right: 30px;
	z-index: 9999;
}

.plain-text {
	white-space: pre-line;
	height: 300px;
}

textarea.model {
	height: 300px !important;
}
</style>
