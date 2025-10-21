<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024
 * @license GNU AGPL version 3 or any later version
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class Version2Date20240724190637 extends SimpleMigrationStep {

	/** @var IDBConnection */
	private $db;

	public function __construct(IDBConnection $db) {
		$this->db = $db;
	}

	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin acciones previas
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		if (!$schema->hasTable('empleados_conf')) {
			$table = $schema->createTable('empleados_conf');

			$table->addColumn('Id_conf', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('Nombre', 'string', [
				'length' => 190,   // seguro para índices en utf8mb4
				'notnull' => true,
			]);

			$table->addColumn('Data', 'string', [
				'length' => 255,
				'notnull' => false,
			]);

			$table->setPrimaryKey(['Id_conf']);
			// Único para poder hacer upsert limpio
			$table->addUniqueIndex(['Nombre'], 'uq_empleados_conf_nombre');
		}

		return $schema;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// Por si el orden de ejecución varía, confirmamos que la tabla exista ya en DB
		$schema = $schemaClosure();
		if (!$schema->hasTable('empleados_conf')) {
			$output->warning('empleados_conf aún no existe en postSchemaChange; omitiendo seeds.');
			return;
		}

		// Claves por defecto (extiéndelo si necesitas más)
		$defaults = [
			'usuario_almacenamiento',
			'automatic_save_note',
			'acumular_vacaciones',
			'modulo_ahorro',
			'modulo_ausencias',
			'ausencias_readonly',
		];

		foreach ($defaults as $nombre) {
			// Intenta insert directo; si ya existe por UNIQUE, ignora
			try {
				$qb = $this->db->getQueryBuilder();
				$qb->insert('empleados_conf')
					->values([
						'Nombre' => $qb->createNamedParameter($nombre),
						'Data'   => $qb->createNamedParameter(null),
					])
					->executeStatement();

				$output->info("Seed empleados_conf.Nombre='$nombre' insertado.");
			} catch (UniqueConstraintViolationException $e) {
				// Ya existe, no pasa nada
				$output->info("Seed empleados_conf.Nombre='$nombre' ya existía; omitido.");
			}
		}
	}
}
