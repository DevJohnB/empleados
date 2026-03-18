<template>
	<div class="top">
		<div class="file-container" @drop.prevent="handleDrop" @dragover.prevent>
			<input ref="fileInput"
				type="file"
				class="file-input"
				multiple
				@change="uploadFile">

			<div style="display: flex; justify-content: space-between; gap: 12px;">
				<NcButton v-if="navigationStack.length > 0" @click="goBack">
					<template #icon>
						<ArrowLeft :size="20" />
					</template>
				</NcButton>
				<div v-if="showLoading" style="display: flex; align-items: center; gap: 8px;">
					<NcLoadingIcon :size="25" :message="t('empleados', 'Processing files…')" />
					<p>{{ t('empleados', 'Uploading file, please wait') }}</p>
				</div>
				<NcButton style="margin-left: auto;" @click="OpenFolder()">
					<template #icon>
						<FolderMoveOutline :size="20" />
					</template>
					{{ t('empleados', 'Open') }}
				</NcButton>
				<NcButton @click="CreateFolder()">
					<template #icon>
						<FolderPlusOutline :size="20" />
					</template>
					{{ t('empleados', 'Create') }}
				</NcButton>
				<NcButton @click="$refs.fileInput.click()">
					<template #icon>
						<CloudUpload :size="20" />
					</template>
					{{ t('empleados', 'Upload') }}
				</NcButton>
				<NcButton @click="reloadFiles()">
					<template #icon>
						<Reload :size="20" />
					</template>
				</NcButton>
			</div>

			<table v-if="files.length > 0" class="file-table">
				<thead>
					<tr>
						<th>📄 {{ t('empleados', 'File') }}</th>
						<th>{{ t('empleados', 'Size') }}</th>
						<th>{{ t('empleados', 'Last modified') }}</th>
					</tr>
				</thead>
				<tbody>
					<tr v-for="file in files" :key="file.id" @click="exploreFolder(file)">
						<td>
							<div class="file-item">
								<FolderOutline v-if="file.isFolder" :size="20" />
								<FilePdfBox v-else-if="file.name.endsWith('.pdf')" :size="20" />
								<ImageIcon v-else-if="file.name.match(/\.(jpg|png|jpeg|gif)$/i)" :size="20" />
								<FileOutline v-else :size="20" />
								<span class="file-name">{{ truncateText(file.name) }}</span>
							</div>
						</td>
						<td>{{ file.isFolder ? '-' : formatSize(file.size) }}</td>
						<td>{{ formatDate(file.modified) }}</td>
					</tr>
				</tbody>
			</table>

			<p v-if="files.length === 0 && !showLoading" class="empty-msg">
				{{ t('empleados', 'No files available.') }}
			</p>
		</div>

		<NcDialog
			:open.sync="showDialogFolder"
			is-form
			:buttons="buttons"
			:name="t('empleados', 'Create folder')"
			@submit="FolderAction()">
			<NcTextField v-model="Folder" :label="t('empleados', 'New name')" required />
		</NcDialog>
	</div>
</template>

<script>
import FolderPlusOutline from 'vue-material-design-icons/FolderPlusOutline.vue'
import FolderMoveOutline from 'vue-material-design-icons/FolderMoveOutline.vue'
import FolderOutline from 'vue-material-design-icons/FolderOutline.vue'
import CloudUpload from 'vue-material-design-icons/CloudUpload.vue'
import FileOutline from 'vue-material-design-icons/FileOutline.vue'
import FilePdfBox from 'vue-material-design-icons/FilePdfBox.vue'
import ImageIcon from 'vue-material-design-icons/Image.vue'
import ArrowLeft from 'vue-material-design-icons/ArrowLeft.vue'
import Reload from 'vue-material-design-icons/Reload.vue'

import { getClient, defaultRootPath } from '@nextcloud/files/dav'
import { upload as Upload } from '@nextcloud/upload'
import { showError, showSuccess, showWarning } from '@nextcloud/dialogs'
import { NcButton, NcDialog, NcTextField, NcLoadingIcon } from '@nextcloud/vue'

import { translate as t } from '@nextcloud/l10n'

export default {
	name: 'FilesTab',
	components: {
		FolderPlusOutline,
		FolderOutline,
		NcButton,
		FileOutline,
		FilePdfBox,
		ImageIcon,
		CloudUpload,
		ArrowLeft,
		FolderMoveOutline,
		NcDialog,
		NcTextField,
		NcLoadingIcon,
		Reload,
	},
	props: {
		data: { type: Object, required: true },
		show: { type: Boolean, required: true },
		empleados: { type: Array, required: true },
	},
	data() {
		return {
			files: [],
			currentPath: null,
			navigationStack: [],
			client: null,
			showDialogFolder: false,
			showLoading: false,
			Folder: '',
			buttons: [{ label: t('empleados', 'Crear'), type: 'primary', nativeType: 'submit' }],
		}
	},
	watch: {
		data(newData) {
			if (newData) {
				const nombre = newData.displayname?.trim() || newData.uid
				this.currentPath = `${defaultRootPath}/EMPLEADOS/${newData.uid} - ${nombre.toUpperCase()}/`
				this.navigationStack = []
				this.fetchFiles()
			}
		},
	},
	mounted() {
		const nombre = this.data.displayname?.trim() || this.data.uid
		this.currentPath = `${defaultRootPath}/EMPLEADOS/${this.data.uid} - ${nombre.toUpperCase()}/`
		this.fetchFiles()
	},
	methods: {
		async fetchFiles() {
			try {
				this.client = getClient()
				this.currentPath = this.normalizeDirectoryPath(this.currentPath)
				const response = await this.client.getDirectoryContents(this.currentPath, { details: true })
				this.files = Array.isArray(response.data)
					? response.data
						.map(file => {
							const name = this.getEntryName(file)
							const path = this.getEntryPath(file) || this.joinDirectoryPath(this.currentPath, name)
							return {
								id: file.id || file.etag || path,
								name,
								size: file.size || 0,
								isFolder: file.type === 'directory',
								location: this.currentPath,
								path,
								modified: file.lastmod || null,
							}
						})
						.filter(file => !this.isCurrentDirectoryEntry(file))
					: []
			} catch (error) {
				showError('❌ Error al obtener archivos: ' + error.message)
			}
		},
		exploreFolder(file) {
			if (file.isFolder) {
				this.navigationStack.push(this.currentPath)
				this.currentPath = this.normalizeDirectoryPath(file.path || this.joinDirectoryPath(this.currentPath, file.name))
				this.fetchFiles()
			}
		},
		goBack() {
			if (this.navigationStack.length) {
				this.currentPath = this.navigationStack.pop()
				this.fetchFiles()
			}
		},
		CreateFolder() {
			this.showDialogFolder = true
		},
		async FolderAction() {
			try {
				const safeFolder = this.Folder.trim().replace(/[<>:"/\\|?*]/g, '_')
				await getClient().createDirectory(`${this.currentPath}${safeFolder}/`)
				this.Folder = ''
				showSuccess('✅ Carpeta creada.')
				this.fetchFiles()
			} catch (e) {
				showError('❌ ' + e.message)
			}
		},
		async subirArchivo(file, destino) {
			const start = performance.now()
			await Upload(destino, file)
			const duracion = (performance.now() - start) / 1000
			const velocidad = file.size / duracion
			let delay = Math.min((file.size / velocidad) * 1.5 * 1000, 5000)
			delay = Math.max(delay, 500)
			for (let i = 0; i < 5; i++) {
				try {
					const remoto = await this.client.stat(this.currentPath + file.name)
					if (remoto?.size === file.size) {
						showSuccess(`✅ Archivo ${file.name} subido correctamente.`)
						return
					}
				} catch {}
				await new Promise(resolve => setTimeout(resolve, delay))
			}
			showWarning(`⚠️ No se pudo confirmar la subida del archivo ${file.name} después de varios intentos.`)
		},
		async uploadFile(event) {
			const files = Array.from(event.target.files)
			if (!files.length) return
			this.showLoading = true
			const destinoBase = this.currentPath.replace(/^\/files\/[^/]+/, '')
			for (const file of files) {
				await this.subirArchivo(file, destinoBase + file.name)
			}
			await this.fetchFiles()
			this.showLoading = false
		},
		async handleDrop(event) {
			const files = Array.from(event.dataTransfer.files)
			if (!files.length) return
			this.showLoading = true
			const destinoBase = this.currentPath.replace(/^\/files\/[^/]+/, '')
			for (const file of files) {
				await this.subirArchivo(file, destinoBase + file.name)
			}
			await this.fetchFiles()
			this.showLoading = false
		},
		truncateText(text, length = 75) {
			return text?.length > length ? text.substring(0, length) + '...' : text
		},
		formatSize(bytes) {
			if (!bytes) return '-'
			if (bytes < 1024) return `${bytes} B`
			const kb = bytes / 1024
			if (kb < 1024) return `${kb.toFixed(2)} KB`
			return `${(kb / 1024).toFixed(2)} MB`
		},
		formatDate(dateString) {
			if (!dateString) return '-'
			return new Date(dateString).toLocaleString('es-MX')
		},
		OpenFolder() {
			const url = `${window.location.origin}/apps/files?dir=${encodeURIComponent(this.getCleanPath(this.currentPath))}`
			window.open(url, '_blank')
		},
		getEntryName(file) {
			const candidate = file.basename || file.name || file.filename || file.href || ''
			const segments = String(candidate).split('/').filter(Boolean)
			return decodeURIComponent(segments.length ? segments[segments.length - 1] : candidate)
		},
		getEntryPath(file) {
			const candidate = file.filename || file.href || file.path || ''
			if (!candidate) {
				return null
			}
			if (typeof candidate === 'string' && candidate.startsWith('http')) {
				return this.normalizeDirectoryPath(new URL(candidate, window.location.origin).pathname)
			}
			if (typeof candidate === 'string' && candidate.startsWith('/')) {
				return this.normalizeDirectoryPath(candidate)
			}
			return this.joinDirectoryPath(this.currentPath, String(candidate))
		},
		getCurrentFolderName(path = this.currentPath) {
			const segments = this.normalizeDirectoryPath(path).split('/').filter(Boolean)
			return segments.length ? segments[segments.length - 1] : ''
		},
		joinDirectoryPath(basePath, childName) {
			const normalizedBase = this.normalizeDirectoryPath(basePath)
			const normalizedChild = String(childName).split('/').filter(Boolean).pop()
			if (!normalizedChild) {
				return normalizedBase
			}
			const currentFolderName = this.getCurrentFolderName(normalizedBase)
			if (normalizedChild === currentFolderName) {
				return normalizedBase
			}
			return this.normalizeDirectoryPath(`${normalizedBase}${normalizedChild}/`)
		},
		normalizeDirectoryPath(path) {
			if (!path) {
				return '/'
			}
			let normalizedPath = decodeURIComponent(String(path))
			if (normalizedPath.startsWith('http')) {
				normalizedPath = new URL(normalizedPath, window.location.origin).pathname
			}
			normalizedPath = normalizedPath.replace(window.location.origin, '')
			normalizedPath = normalizedPath.replace(/\/{2,}/g, '/')
			return normalizedPath.endsWith('/') ? normalizedPath : `${normalizedPath}/`
		},
		isCurrentDirectoryEntry(file) {
			if (!file.isFolder) {
				return false
			}
			const currentPath = this.normalizeDirectoryPath(this.currentPath)
			const filePath = this.normalizeDirectoryPath(file.path)
			if (filePath === currentPath) {
				return true
			}
			return file.name === '.' || file.name === '..'
		},
		getCleanPath(path) {
			const seg = path.split('/').filter(Boolean)
			return seg.length > 2 ? '/' + seg.slice(2).join('/') + '/' : path
		},
		reloadFiles() {
			this.fetchFiles()
		},
	},
}
</script>

<style scoped>
.file-container {
	margin: 0 auto;
	padding: 20px;
	background: white;
	border-radius: 8px;
	box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
	text-align: center;
	min-height: 200px;
	border: 2px #ccc;
}
.file-table {
	width: 100%;
	border-collapse: collapse;
	margin-top: 10px;
}
.file-table th, .file-table td {
	border-bottom: 1px solid #ddd;
	padding: 10px;
	text-align: left;
}
.file-table th { background-color: #f4f4f4; font-weight: bold; }
.file-table tr:hover { background-color: #f9f9f9; }
.file-input { display: none; }
.empty-msg { color: #888; text-align: center; }
.file-item {
	display: flex;
	align-items: center;
	gap: 8px;
}
.file-name { white-space: nowrap; }
</style>
