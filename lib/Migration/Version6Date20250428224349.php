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

class Version6Date20250428224349 extends SimpleMigrationStep {

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

			// Índices útiles (no se cambian tipos)
			$table->addIndex(['id_user'], 'user_ahorro_uid');
			$table->addIndex(['id_permision'], 'user_ahorro_perm');
			$table->addIndex(['state'], 'user_ahorro_state');
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
