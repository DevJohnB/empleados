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

class Version7Date20250428230520 extends SimpleMigrationStep {

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

		if (!$schema->hasTable('historial_ahorro')) {
			$table = $schema->createTable('historial_ahorro');

			$table->addColumn('id_historial', 'integer', [
				'autoincrement' => true,
				'notnull' => true,
			]);

			$table->addColumn('id_ahorro', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('cantidad_solicitada', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('cantidad_total', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('fecha_solicitud', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('estado', 'string', [
				'notnull' => false,
			]);

			$table->addColumn('nota', 'string', [
				'notnull' => false,
			]);

			$table->setPrimaryKey(['id_historial']);

			// Índices útiles (no cambian tipos)
			$table->addIndex(['id_ahorro'], 'hist_ahorro_id');
			$table->addIndex(['estado'], 'hist_ahorro_estado');
			$table->addIndex(['fecha_solicitud'], 'hist_ahorro_fecha');
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
