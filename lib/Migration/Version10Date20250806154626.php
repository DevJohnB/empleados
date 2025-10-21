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

class Version10Date20250806154626 extends SimpleMigrationStep {

	/** @var IDBConnection */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// nada
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		// no cambios de esquema en esta versión
		return null;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// 1) Asegura que existe la tabla (por si esta versión corre en un entorno incompleto)
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();
		if (!$schema->hasTable('empleados_conf')) {
			$output->info('Tabla empleados_conf no existe; salto seeding ausencias_readonly.');
			return;
		}

		// 2) Idempotencia: inserta solo si no existe
		$nombre = 'ausencias_readonly';

		$check = $this->db->getQueryBuilder();
		$check->select('Id_conf')
			->from('empleados_conf')
			->where(
				$check->expr()->eq('Nombre', $check->createNamedParameter($nombre))
			);

		$exists = $check->executeQuery()->fetchOne();
		if ($exists !== false) {
			$output->info("empleados_conf.Nombre '{$nombre}' ya existe; nada que hacer.");
			return;
		}

		$ins = $this->db->getQueryBuilder();
		$ins->insert('empleados_conf')
			->values([
				'Nombre' => $ins->createNamedParameter($nombre),
				// 'Data' => $ins->createNamedParameter(null), // si quieres setearlo explícitamente
			])
			->executeStatement();

		$output->info("Insertado empleados_conf.Nombre='{$nombre}'.");
	}
}
