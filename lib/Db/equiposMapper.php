<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use DateTime;
use OCP\AppFramework\Db\MultipleObjectsReturnedException;
use OCP\AppFramework\Db\QBMapper;
use OCP\DB\Exception;
use OCP\DB\QueryBuilder\IQueryBuilder;
use OCP\IDBConnection;

use OCP\AppFramework\Db\DoesNotExistException;


class equiposMapper extends QBMapper {
	public function __construct(IDBConnection $db) {
		parent::__construct($db, 'equipos', equipos::class);
	}

	public function GetEquiposList(): array {
		$qb = $this->db->getQueryBuilder();

		/* 
			SELECT d.nombre, COUNT(e.id_empleados) AS cantidad_empleados
			FROM oc_departamentos d
			LEFT JOIN oc_empleados e ON d.Nombre = e.Id_equipo
			GROUP BY d.Id_equipo;
		*/
		$qb->select('d.Id_equipo', 'd.Id_jefe_equipo','d.Nombre')
			->selectAlias($qb->createFunction('COUNT(e.Id_empleados)'), 'cantidad_empleados')
			->from($this->getTableName(), 'd')
			->leftJoin('d', 'empleados', 'e', 'd.Id_equipo = e.Id_equipo')
			->groupBy('d.Id_equipo');
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function GetEquipoJefe($id): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('d.Id_equipo', 'd.Id_jefe_equipo','d.Nombre')
			->selectAlias($qb->createFunction('COUNT(e.Id_empleados)'), 'cantidad_empleados')
			->from($this->getTableName(), 'd')
			->where($qb->expr()->eq('d.Id_equipo', $qb->createNamedParameter($id)))
			->leftJoin('d', 'empleados', 'e', 'd.Id_equipo = e.Id_equipo')
			->groupBy('d.Id_equipo');
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function CheckExistEquipos($id_departamentos): array {
		$qb = $this->db->getQueryBuilder();

		$qb->select('*')
			->from($this->getTableName())
			->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($id_departamentos)));
			
		$result = $qb->execute();
		$users = $result->fetchAll();
		$result->closeCursor();
	
		return $users;
	}

	public function deleteByIdEmpleado(int $id_departamentos): void {
		$qb = $this->db->getQueryBuilder();

		$qb->delete($this->getTableName())
			->where($qb->expr()->eq('Nombre', $qb->createNamedParameter($Nombre)));

		$result = $qb->execute();
	}

	public function updateEquipos($Id_equipo, $Id_jefe_equipo): void {
		$timestamp = date('Y-m-d');

		if(empty($Id_equipo) && $Id_equipo != 0){ $Id_equipo = null; }
		if(empty($Id_jefe_equipo) && $Id_jefe_equipo != 0){ $Id_jefe_equipo = null; }
		if(empty($Nombre) && $Nombre != 0){ $Nombre = null; }
		$query = $this->db->getQueryBuilder();
		$query->update($this->getTableName())
			->set('Id_jefe_equipo', $query->createNamedParameter($Id_jefe_equipo))
			->set('updated_at', $query->createNamedParameter($timestamp))
			->where($query->expr()->eq('Id_equipo', $query->createNamedParameter($Id_equipo)));
	
		$query->execute();
	}

	// en equiposMapper.php
	public function getById(string $id): ?array {
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
		->from($this->getTableName())
		->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($id)))
		->setMaxResults(1);

		$res = $qb->execute(); // compat DBAL antiguo
		$row = $res->fetch(\PDO::FETCH_ASSOC); // <-- clave: usar fetch(PDO::FETCH_ASSOC)
		if (method_exists($res, 'closeCursor')) { $res->closeCursor(); }
		return $row ?: null;
	}




	public function EliminarEquipo(string $Id_equipo): ?array {
		// intenta leer la fila
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
		->from($this->getTableName())
		->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($Id_equipo)));

		$row = $qb->executeQuery()->fetchAssociative();
		if (!$row) {
			return null;
		}

		// borrar
		$qb2 = $this->db->getQueryBuilder();
		$qb2->delete($this->getTableName())
			->where($qb2->expr()->eq('Id_equipo', $qb2->createNamedParameter($Id_equipo)));
		// use executeStatement en DBAL moderno
		$qb2->executeStatement();

		return $row;
	}

	public function deleteByIdReturningRow(string $Id_equipo): ?array {
		// 1) leer la fila (compatibilidad con distintas versiones)
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
		->from($this->getTableName())
		->where($qb->expr()->eq('Id_equipo', $qb->createNamedParameter($Id_equipo)));

		$result = $qb->execute();

		// compat fetch
		$row = null;
		if ($result === false) {
			return null;
		}

		if (method_exists($result, 'fetchAssociative')) {
			// DBAL newer
			$row = $result->fetchAssociative();
		} elseif (method_exists($result, 'fetch')) {
			// PDO-style
			$row = $result->fetch(\PDO::FETCH_ASSOC);
		} elseif (method_exists($result, 'fetchArray')) {
			// algunas implementaciones
			$row = $result->fetchArray();
		} elseif (method_exists($result, 'fetchRow')) {
			$row = $result->fetchRow();
		} else {
			// fallback: iterador (si resulta iterable)
			foreach ($result as $r) {
				$row = $r;
				break;
			}
		}

		if (!$row) {
			return null;
		}

		// 2) borrar (usar executeStatement si existe)
		$qb2 = $this->db->getQueryBuilder();
		$qb2->delete($this->getTableName())
			->where($qb2->expr()->eq('Id_equipo', $qb2->createNamedParameter($Id_equipo)));

		if (method_exists($qb2, 'executeStatement')) {
			$qb2->executeStatement();
		} else {
			$qb2->execute();
		}

		return $row;
	}


}