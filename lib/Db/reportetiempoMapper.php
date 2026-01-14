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
	public function findById(int $id, int $limit = 20, int $offset = 0, $periodo_inicio = null, $periodo_fin = null, $anio = null): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('id_empleado', $qb->createNamedParameter($id, IQueryBuilder::PARAM_INT))
			)
			->orderBy('id_reporte', 'DESC');

		// Filtro por rango de meses del año
		if ($anio !== null && $periodo_inicio !== null && $periodo_fin !== null) {
			$periodo_inicio = max(1, min(12, (int)$periodo_inicio));
			$periodo_fin    = max(1, min(12, (int)$periodo_fin));

			// si vienen invertidos, los acomodamos
			if ($periodo_inicio > $periodo_fin) {
				[$periodo_inicio, $periodo_fin] = [$periodo_fin, $periodo_inicio];
			}

			$inicio = sprintf('%04d-%02d-01', (int)$anio, $periodo_inicio);

			$fin = (new \DateTimeImmutable(sprintf('%04d-%02d-01', (int)$anio, $periodo_fin)))
				->modify('last day of this month')
				->format('Y-m-d');

			$qb->andWhere($qb->expr()->gte('fecha_registro', $qb->createNamedParameter($inicio)));
			$qb->andWhere($qb->expr()->lte('fecha_registro', $qb->createNamedParameter($fin)));
		}

		if ($limit > 0) {
			$qb->setMaxResults($limit)
			->setFirstResult($offset);
		}

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

	public function updateReporte($id_reporte, $id_actividad, $id_empleado, $descripcion, $tiemporegistrado, $fecha): void {
		$timestamp = date('Y-m-d');

		$query = $this->db->getQueryBuilder();
		$result = $query->update($this->getTableName())
			->set('id_actividad', $query->createNamedParameter($id_actividad))
			->set('descripcion', $query->createNamedParameter($descripcion))
			->set('tiempo_registrado', $query->createNamedParameter($tiemporegistrado))
			->set('fecha_registro', $query->createNamedParameter($fecha))
			->set('updated_at', $query->createNamedParameter($timestamp))
			->where($query->expr()->eq('id_reporte', $query->createNamedParameter($id_reporte)))
			->andWhere($query->expr()->eq('id_empleado', $query->createNamedParameter($id_empleado)))
			// condición de 40 minutos
			->andWhere('TIMESTAMPDIFF(MINUTE, created_at, NOW()) < 40')
			->execute();

		if ($result === 0) {
			throw new \Exception("Update bloqueado: el reporte ya tiene más de 40 minutos y no se puede modificar.");
		}
	}

	/* SKELETONS para que luego los llenes
	public function findByEmpleado(string $uid, int $limit = 100, int $offset = 0): array { ... }
	public function findByRangoFecha(string $desde, string $hasta): array { ... }
	public function findPorClienteActividad(?int $idCliente, ?int $idActividad, int $limit = 100, int $offset = 0): array { ... }
	*/
}
