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

class Version8Date20250512190334 extends SimpleMigrationStep {

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

		// tipo_ausencia: agregar solicitar_prima_vacacional si falta
		if ($schema->hasTable('tipo_ausencia')) {
			$table = $schema->getTable('tipo_ausencia');
			if (!$table->hasColumn('solicitar_prima_vacacional')) {
				$table->addColumn('solicitar_prima_vacacional', 'integer', [
					'notnull' => true,
					'default' => 0,
				]);
			}
		}

		// historial_ausencias: agregar notas si falta
		if ($schema->hasTable('historial_ausencias')) {
			$table = $schema->getTable('historial_ausencias');
			if (!$table->hasColumn('notas')) {
				$table->addColumn('notas', 'string', [
					'notnull' => false,
					'length' => 255,
				]);
			}
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
