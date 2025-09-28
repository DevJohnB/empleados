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

class Version2Date20240724190637 extends SimpleMigrationStep {

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
		// No pre-schema changes needed
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

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		$defaults = ['usuario_almacenamiento', 'automatic_save_note', 'acumular_vacaciones', 'modulo_ahorro'];

		foreach ($defaults as $nombre) {
			// 1) Verificar si ya existe
			$check = $this->db->getQueryBuilder();
			$check->select('Id_conf')
				->from('empleados_conf')
				->where(
					$check->expr()->eq('Nombre', $check->createNamedParameter($nombre))
				);

			$exists = $check->executeQuery()->fetchOne();
			if ($exists !== false) {
				continue; // ya insertado
			}

			// 2) Insertar
			$ins = $this->db->getQueryBuilder();
			$ins->insert('empleados_conf')
				->values([
					'Nombre' => $ins->createNamedParameter($nombre),
					// 'Data' => $ins->createNamedParameter(null), // si necesitas setearlo explícitamente
				])
				->executeStatement();
		}
	}
}
