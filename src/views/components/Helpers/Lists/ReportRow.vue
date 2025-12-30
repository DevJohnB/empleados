<template>
	<div class="row">
		<NcListItem
			:name="source.actividadNombre"
			bold
			:force-display-actions="true">
			<template #subname>
				{{ source.clienteNombre }}
			</template>
			<template #actions>
				<NcActionButton>
					Button one
				</NcActionButton>
				<NcActionButton>
					Button two
				</NcActionButton>
				<NcActionButton>
					Button three
				</NcActionButton>
			</template>
		</NcListItem>
	</div>
</template>

<script>
import {
	NcListItem,
} from '@nextcloud/vue'

export default {
	name: 'ReportRow',
	components: {
		NcListItem,
	},
	props: {
		source: { type: Object, required: true },
		index: { type: Number, required: false, default: null },
	},
}
</script>

<style scoped>
.row { padding: 10px 12px; border-bottom: 1px solid var(--color-border); }
.title { font-weight: 600; }
.meta { opacity: .7; font-size: 12px; margin-top: 2px; }
</style>
