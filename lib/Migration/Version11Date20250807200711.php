<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;

class Version11Date20250807200711 extends SimpleMigrationStep {

	/** @var IDBConnection */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin acciones previas
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		// Verificar y crear tablas si no existen

		if (!$schema->hasTable('empleados')) {
			$table = $schema->createTable('empleados');

			$table->addColumn('Id_empleados', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('Id_user', 'string', [
				'notnull' => true,
			]);

			$table->addColumn('Numero_empleado', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Ingreso', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Correo_contacto', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Id_departamento', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Id_puesto', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Id_equipo', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Id_gerente', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Id_socio', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Fondo_clave', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Fondo_ahorro', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Numero_cuenta', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Equipo_asignado', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Sueldo', 'decimal', [
				'notnull' => false,
			]);

			$table->addColumn('Notas', 'text', [
				'notnull' => false,
			]);

			$table->addColumn('Fecha_nacimiento', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Estado', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Direccion', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Estado_civil', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Telefono_contacto', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Curp', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Rfc', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Imss', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Genero', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Contacto_emergencia', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Numero_emergencia', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['Id_empleados']);
			$table->addIndex(['Id_empleados'], 'Id_empleados');
			$table->addIndex(['Id_gerente'], 'idx_id_gerente');
			$table->addIndex(['Id_socio'], 'idx_id_socio');
		}

		if (!$schema->hasTable('puestos')) {
			$table = $schema->createTable('puestos');

			$table->addColumn('Id_puestos', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['Id_puestos']);
			$table->addIndex(['Id_puestos'], 'Id_puestos');
		}

		if (!$schema->hasTable('departamentos')) {
			$table = $schema->createTable('departamentos');

			$table->addColumn('Id_departamento', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('Id_padre', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['Id_departamento']);
			$table->addIndex(['Id_departamento'], 'Id_departamento');
		}

		if (!$schema->hasTable('empleados_conf')) {
			$table = $schema->createTable('empleados_conf');

			$table->addColumn('Id_conf', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Data', 'string', [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['Id_conf']);
			$table->addIndex(['Id_conf'], 'Id_conf');
		}

		if (!$schema->hasTable('aniversarios')) {
			$table = $schema->createTable('aniversarios');

			$table->addColumn('id_aniversario', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('numero_aniversario', 'integer', ['notnull' => true]);
			$table->addColumn('fecha_de', 'datetime', ['notnull' => false]);
			$table->addColumn('fecha_hasta', 'datetime', ['notnull' => false]);
			$table->addColumn('dias', 'decimal', ['precision' => 5, 'scale' => 2, 'notnull' => true]);

			// Sin DEFAULT aquí; se asegura por reparador específico de motor
			$table->addColumn('timestamp', 'datetime', ['notnull' => true]);

			$table->setPrimaryKey(['id_aniversario']);
			$table->addIndex(['numero_aniversario'], 'aniv_idx_numero');
		}

		if (!$schema->hasTable('tipo_ausencia')) {
			$table = $schema->createTable('tipo_ausencia');

			$table->addColumn('id_tipo_ausencia', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('nombre', 'string', ['length' => 255, 'notnull' => true]);
			$table->addColumn('descripcion', 'text', ['notnull' => false]);
			$table->addColumn('solicitar_archivo', 'integer', ['notnull' => true]);
			$table->addColumn('solicitar_prima_vacacional', 'integer', ['notnull' => true]);

			$table->setPrimaryKey(['id_tipo_ausencia']);
			$table->addIndex(['nombre'], 'tipo_idx_nombre');
		}

		if (!$schema->hasTable('ausencias')) {
			$table = $schema->createTable('ausencias');

			$table->addColumn('id_ausencias', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('id_empleado', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('id_aniversario', 'integer', ['unsigned' => true, 'notnull' => false]);
			$table->addColumn('dias_disponibles', 'decimal', ['precision' => 5, 'scale' => 2, 'notnull' => false, 'default' => 0.00]);
			$table->addColumn('prima_vacacional', 'boolean', ['notnull' => false, 'default' => 0]);

			// Sin DEFAULT aquí; se asegura por reparador específico de motor
			$table->addColumn('timestamp', 'datetime', ['notnull' => true]);

			$table->setPrimaryKey(['id_ausencias']);
			$table->addUniqueIndex(['id_empleado'], 'uniq_aus_empleado');
		}

		if (!$schema->hasTable('historial_ausencias')) {
			$table = $schema->createTable('historial_ausencias');

			$table->addColumn('id_historial_ausencias', 'integer', ['autoincrement' => true, 'unsigned' => true]);
			$table->addColumn('id_ausencias', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('id_aniversario', 'integer', ['unsigned' => true, 'notnull' => false]);
			$table->addColumn('id_tipo_ausencia', 'integer', ['unsigned' => true, 'notnull' => true]);
			$table->addColumn('fecha_de', 'datetime', ['notnull' => true]);
			$table->addColumn('fecha_hasta', 'datetime', ['notnull' => true]);
			$table->addColumn('prima_vacacional', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('archivo', 'string', ['length' => 255, 'notnull' => false]);

			// Sin DEFAULT aquí; se asegura por reparador específico de motor
			$table->addColumn('timestamp', 'datetime', ['notnull' => true]);

			$table->addColumn('a_socio', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('a_gerente', 'boolean', ['notnull' => false, 'default' => 0]);
			$table->addColumn('a_capital_humano', 'boolean', ['notnull' => false, 'default' => 0]);

			$table->setPrimaryKey(['id_historial_ausencias']);
			$table->addIndex(['id_ausencias'], 'hist_idx_aus');
		}

		if (!$schema->hasTable('equipos')) {
			$table = $schema->createTable('equipos');

			$table->addColumn('Id_equipo', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('Id_jefe_equipo', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['Id_equipo']);
			$table->addIndex(['Id_equipo'], 'Id_equipo');
		}

		if (!$schema->hasTable('user_ahorro')) {
			$table = $schema->createTable('user_ahorro');

			$table->addColumn('id_ahorro', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('id_user', 'integer', [
				'notnull' => true,
			]);
			$table->addColumn('id_permision', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('state', 'string', [
				'notnull' => true,
			]);
			$table->addColumn('last_modified', 'string', [
				'notnull' => true,
			]);
			$table->setPrimaryKey(['id_ahorro']);
			$table->addIndex(['id_user'], 'user_ahorro_uid');
		}

		if (!$schema->hasTable('historial_ahorro')) {
			$table = $schema->createTable('historial_ahorro');

			$table->addColumn('id_historial', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);
			$table->addColumn('id_ahorro', 'string', []);
			$table->addColumn('cantidad_solicitada', 'string', []);
			$table->addColumn('cantidad_total', 'string', []);
			$table->addColumn('fecha_solicitud', 'string', []);
			$table->addColumn('estado', 'string', []);
			$table->addColumn('nota', 'string', []);
			$table->setPrimaryKey(['id_historial']);
		}

		// Insertar datos iniciales si no existen (sin cambios en esta versión)

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin acciones posteriores
	}
}
