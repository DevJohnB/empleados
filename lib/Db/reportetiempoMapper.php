<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;

class reportetiempoMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'empleados_rep_tiempos', ReporteTiempo::class);
	}

	/** @throws DoesNotExistException|MultipleObjectsReturnedException */
	public function findById(int $id): ReporteTiempo {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id_reporte', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);
		return $this->findEntity($qb);
	}

	/** @return ReporteTiempo[] */
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
