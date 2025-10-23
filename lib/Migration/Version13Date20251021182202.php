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

/**
 * FIXME Auto-generated migration step: Please modify to your needs!
 */
class Version13Date20251021182202 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
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

		// Tabla REPORTE TIEMPOS
		if (!$schema->hasTable('empleados_rep_tiempos')) {
			$table = $schema->createTable('empleados_rep_tiempos');
			$table->addColumn('id_reporte', 'integer', ['autoincrement' => true, 'notnull' => true]);
			$table->addColumn('id_empleado', 'string', ['length' => 64, 'notnull' => true]);
			$table->addColumn('id_cliente', 'integer', ['notnull' => false]);
			$table->addColumn('id_actividad', 'integer', ['notnull' => false]);
			$table->addColumn('descripcion', 'text', ['notnull' => false]);
			$table->addColumn('tiempo_registrado', 'decimal', ['precision' => 8, 'scale' => 2, 'notnull' => true]);
			$table->addColumn('fecha_registro', 'date', ['notnull' => true]);
			$table->addColumn('created_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP'
			]);
			$table->addColumn('updated_at', 'datetime', [
				'notnull' => true,
				'default' => 'CURRENT_TIMESTAMP'
			]);

			$table->setPrimaryKey(['id_reporte']);
			$table->addForeignKeyConstraint('oc_empleados_clientes', ['id_cliente'], ['id_cliente'], ['onDelete' => 'SET NULL']);
			$table->addForeignKeyConstraint('oc_empleados_actividades', ['id_actividad'], ['id_actividad'], ['onDelete' => 'SET NULL']);

		}
		
		return $schema;	
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
	}
}
