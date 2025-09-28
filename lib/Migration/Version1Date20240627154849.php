<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Luis Angel Alvarado Hernandez
 * @license GNU AGPL version 3 or any later version
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version1Date20240627154849 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin pre cambios
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

		/**
		 * Tabla: empleados
		 */
		if (!$schema->hasTable('empleados')) {
			$table = $schema->createTable('empleados');

			$table->addColumn('Id_empleados', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			// uid de Nextcloud suele ser <= 64
			$table->addColumn('Id_user', 'string', [
				'notnull' => true,
				'length' => 64,
			]);

			$table->addColumn('Numero_empleado', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			// fechas como string (mantengo tu decisión); idealmente datetime/integer en una migración futura
			$table->addColumn('Ingreso', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Correo_contacto', 'string', [
				'notnull' => false,
				'length' => 190, // seguro para índice
			]);

			// NOTA: aparentan ser FKs lógicas; las dejas como string
			$table->addColumn('Id_departamento', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Id_puesto', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Id_equipo', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Id_gerente', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Id_socio', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Fondo_clave', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Fondo_ahorro', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Numero_cuenta', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Equipo_asignado', 'string', [
				'notnull' => false,
				'length' => 190,
			]);

			$table->addColumn('Sueldo', 'decimal', [
				'notnull' => false,
				'precision' => 12,
				'scale' => 2,
			]);

			$table->addColumn('Notas', 'text', [
				'notnull' => false,
			]);

			$table->addColumn('Fecha_nacimiento', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Estado', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Direccion', 'string', [
				'notnull' => false,
				'length' => 190,
			]);

			$table->addColumn('Estado_civil', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Telefono_contacto', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Curp', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Rfc', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Imss', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Genero', 'string', [
				'notnull' => false,
				'length' => 32,
			]);

			$table->addColumn('Contacto_emergencia', 'string', [
				'notnull' => false,
				'length' => 190,
			]);

			$table->addColumn('Numero_emergencia', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->setPrimaryKey(['Id_empleados']);

			// Índices útiles
			$table->addIndex(['Id_empleados'], 'Id_empleados'); // redundante pero lo mantengo
			$table->addIndex(['Id_user'], 'idx_id_user');
			$table->addIndex(['Numero_empleado'], 'idx_numero_empleado');
			$table->addIndex(['Correo_contacto'], 'idx_correo_contacto');
			$table->addIndex(['Id_departamento'], 'idx_id_departamento');
			$table->addIndex(['Id_puesto'], 'idx_id_puesto');
			$table->addIndex(['Id_equipo'], 'idx_id_equipo');
			$table->addIndex(['Id_gerente'], 'idx_id_gerente');
			$table->addIndex(['Id_socio'], 'idx_id_socio');
		}

		/**
		 * Tabla: puestos
		 */
		if (!$schema->hasTable('puestos')) {
			$table = $schema->createTable('puestos');

			$table->addColumn('Id_puestos', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
				'length' => 190,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->setPrimaryKey(['Id_puestos']);
			$table->addIndex(['Id_puestos'], 'Id_puestos');
			$table->addIndex(['Nombre'], 'idx_puestos_nombre');
		}

		/**
		 * Tabla: departamentos
		 */
		if (!$schema->hasTable('departamentos')) {
			$table = $schema->createTable('departamentos');

			$table->addColumn('Id_departamento', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('Id_padre', 'string', [
				'notnull' => false,
				'length' => 64,
			]);

			$table->addColumn('Nombre', 'string', [
				'notnull' => false,
				'length' => 190,
			]);

			$table->addColumn('created_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->addColumn('updated_at', 'string', [
				'notnull' => true,
				'length' => 32,
			]);

			$table->setPrimaryKey(['Id_departamento']);
			$table->addIndex(['Id_departamento'], 'Id_departamento');
			$table->addIndex(['Nombre'], 'idx_departamentos_nombre');
			$table->addIndex(['Id_padre'], 'idx_departamentos_padre');
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin seeds en esta versión
	}
}
