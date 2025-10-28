<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;
use OCP\AppFramework\Db\DoesNotExistException;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\DB\QueryBuilder\IQueryBuilder;

class clientesMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'empleados_clientes', Cliente::class);
	}

	/** Básico: obtener por ID */
	/** @throws DoesNotExistException|MultipleObjectsReturnedException */
	public function findById(int $id): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id_cliente', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);
		$result = $qb->execute();
		$data = $result->fetchAll();
		$result->closeCursor();
	
		return $data;
	}

	/** Listado simple */
	/** @return Cliente[] */
	public function findAll(int $limit = 100, int $offset = 0): array {
		$qb = $this->db->getQueryBuilder();
		$qb->select(
				'p.id_cliente',
				'p.nombre',
				'p.detalles',
				'p.cliente_padre',
				$qb->createFunction('COUNT(c.id_cliente) AS child_count')
			)
			->from($this->getTableName(), 'p')
			->leftJoin('p', $this->getTableName(), 'c',
				$qb->expr()->eq('c.cliente_padre', 'p.id_cliente')
			)
			->groupBy('p.id_cliente', 'p.nombre', 'p.detalles', 'p.cliente_padre')
			->orderBy('p.id_cliente', 'DESC')
			->setMaxResults($limit)
			->setFirstResult($offset);

		$result = $qb->execute();
		$data = $result->fetchAll();
		$result->closeCursor();
	
		return $data;
	}

	/** Borrado rápido por ID */
	public function deleteById(int $id): void {
		$qb = $this->db->getQueryBuilder();
		$qb->delete($this->getTableName())
			->where(
				$qb->expr()->eq('id_cliente', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			);
		$qb->executeStatement(); // usa ->execute() si tu versión lo requiere
	}

	public function updateCliente(int $id_cliente, string $nombre, string $detalles, float $tiempoestimado): void {	
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('nombre', $query->createNamedParameter($nombre))
			->set('detalles', $query->createNamedParameter($detalles))
			->set('tiempo_estimado', $query->createNamedParameter($tiempoestimado))
			->where($query->expr()->eq('id_cliente', $query->createNamedParameter($id_cliente)));
	
		$query->execute();
	}
}
