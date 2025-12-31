<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;

class reportetiempoMapper extends QBMapper {
	protected string $primaryKey = 'id_reporte';
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'empleados_rep_tiempos', reportetiempo::class);
	}

	/** @return reportetiempo[] */
	public function findById(int $id, int $limit = 20, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('id_reporte', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset)
			->where(
				$qb->expr()->eq('id_empleado', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);

		$result = $qb->executeQuery();
		$rows = $result->fetchAll();
		$result->closeCursor();

		return $rows;
	}

	/** @return reportetiempo[] */
	public function findAll(int $limit = 100, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->orderBy('id_reporte', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);
		return $this->findEntities($qb);
	}

	public function deleteById(int $id): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where(
				$qb->expr()->eq('id_reporte', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);
		$qb->executeStatement();
	}

	/* SKELETONS para que luego los llenes
	public function findByEmpleado(string $uid, int $limit = 100, int $offset = 0): array { ... }
	public function findByRangoFecha(string $desde, string $hasta): array { ... }
	public function findPorClienteActividad(?int $idCliente, ?int $idActividad, int $limit = 100, int $offset = 0): array { ... }
	*/
}
