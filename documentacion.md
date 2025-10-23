- actualizar seccion de detalles de empleado
data es una variable array que contiene todos los datos del empleado
this.$bus.emit('send-data', data)

----------------------------------------------------------------------------------
- actualizar loading state
esto solo cambia el valor de show, cambiar a true o false segun la necesidad
this.$bus.emit('show', false)

----------------------------------------------------------------------------------
- actualizar lista de empleados
this.$bus.emit('getall')

----------------------------------------------------------------------------------

en cada archivo de vue puedes esperar un evento con

		this.$bus.on('send-data', (data) => {
			this.data_empleado = data
		})

cambiando el send-data por el nombre de tu evento
data equivale a la informacion enviada por evento emitido


----------------------------------------------------------------------------------



enviar correos
solo agrega el helper para poder enviar notificaciones por correo (en casos muy puntuales solo usar esto, no en masivos)
 $this->mailHelper->enviarCorreo(
            'usuario@dominio.com',
            'Nueva solicitud de ausencia',
            [
                'Hola Luis,',
                'Se ha registrado una nueva ausencia.',
                'Tipo: Falta injustificada.',
                'Desde: 2025-07-28',
                'Hasta: 2025-08-01'
            ]
        );



para usar listas agrega del helper

import List from '../Helpers/Lists/List.vue'
<List
			:loading="loading"
			:detalles="detalles" />


const keyMap = {
					id_response: 'id',
					name_response: 'name',
					subname_response: 'subname',
					cantidades_response: 'count',
                    imagen_response: 'image'
				}

				const renameKeys = (obj, map) =>
					Object.fromEntries(Object.entries(obj).map(([k, v]) => [map[k] ?? k, v]))

				const arr = Array.isArray(response?.data?.ocs?.data) ? response.data.ocs.data : []

				this.detalles = arr.map(o => renameKeys(o, keyMap))

				this.loading = false