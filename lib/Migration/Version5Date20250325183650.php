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

class Version5Date20250325183650 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin preacciones
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

		// Tabla: equipos
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

			// Índices útiles (no cambian tipos)
			$table->addIndex(['Id_equipo'], 'Id_equipo');
			$table->addIndex(['Id_jefe_equipo'], 'idx_id_jefe_equipo');
			$table->addIndex(['Nombre'], 'idx_nombre_equipo');
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin seeds
	}
}
