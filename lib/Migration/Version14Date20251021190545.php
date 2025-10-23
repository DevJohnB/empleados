<?php
declare(strict_types=1);

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use OCP\IDBConnection;
use OCP\DB\QueryBuilder\IQueryBuilder;

class Version14Date20251021190545 extends SimpleMigrationStep {

	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// nada
	}

	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		// No hay cambios de esquema; solo insertaremos datos en postSchemaChange.
		return null;
	}

	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		/** @var IDBConnection $db */
		$db = \OC::$server->get(IDBConnection::class);

		// Usa el nombre SIN prefijo; QB lo añade (oc_) automáticamente.
		$table = 'empleados_conf';

		$this->ensure($db, $table, 'modulo_clientes', 'false');
		$this->ensure($db, $table, 'modulo_reporte_tiempos', 'false');
	}

	private function ensure(IDBConnection $db, string $table, string $nombre, string $data): void {
		$qb = $db->getQueryBuilder();
		$qb->select($qb->createFunction('1'))
			->from($table) // <-- sin prefijo
			->where(
				$qb->expr()->eq('Nombre', $qb->createNamedParameter($nombre, IQueryBuilder::PARAM_STR))
			);

		$exists = method_exists($qb, 'executeQuery')
			? $qb->executeQuery()->fetchOne()
			: $qb->execute()->fetchOne();

		if (!$exists) {
			$ins = $db->getQueryBuilder();
			$ins->insert($table)
				->values([
					'Nombre' => $ins->createNamedParameter($nombre, IQueryBuilder::PARAM_STR),
					'Data'   => $ins->createNamedParameter($data,   IQueryBuilder::PARAM_STR),
				]);

			if (method_exists($ins, 'executeStatement')) {
				$ins->executeStatement();
			} else {
				$ins->execute();
			}
		}
	}
}
