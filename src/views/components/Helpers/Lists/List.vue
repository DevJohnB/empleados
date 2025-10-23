<template id="List">
	<NcAppContent v-if="loading" name="Loading">
		<NcEmptyContent class="empty-content" :name="t('empleados', 'Loading')">
			<template #icon>
				<NcLoadingIcon :size="20" />
			</template>
		</NcEmptyContent>
	</NcAppContent>

	<NcAppContent v-else name="Loading">
		<!-- contacts list -->
		<template #list>
			<FullList
				:detalles="detalles"
				:reload-bus="reloadBus" />
		</template>
		{{ select }}
		<!-- main contacts details -->
		<!-- AreasDetails :data="data_areas" :people-area="peopleArea" / -->
	</NcAppContent>
</template>

<script>
// agregados
import FullList from './FullList.vue'
// import AreasDetails from './perfil/AreasDetails.vue'

// import axios from '@nextcloud/axios'
import mitt from 'mitt'
import { translate as t } from '@nextcloud/l10n'

import {
	NcEmptyContent,
	NcAppContent,
	NcLoadingIcon,
} from '@nextcloud/vue'

export default {
	name: 'List',
	components: {
		FullList,
		NcEmptyContent,
		NcAppContent,
		NcLoadingIcon,
		// AreasDetails,
	},

	props: {
		loading: { type: Boolean, required: true },
		detalles: { type: Array, required: true },
		select: { type: Array, required: true },
		// reloadBus: { type: Object, required: true },
	},

	data() {
		return {
			reloadBus: mitt(),
		}
	},

	methods: {
		t, // exponer i18n a la plantilla
	},
}
</script>

<style scoped lang="scss">
	.container {
		padding-left: 60px;
	}
	.board-title {
		padding-left: 60px;
		margin-right: 10px;
		margin-top: 14px;
		font-size: 25px;
		display: flex;
		align-items: center;
		font-weight: bold;
		.icon {
			margin-right: 8px;
		}
	}
</style>
