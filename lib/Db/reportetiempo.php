<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;
class reportetiempo extends Entity {

	protected ?int $id_reporte = null;
	protected ?int $id_empleado = null;
	protected ?int $id_cliente = null;
	protected ?int $id_actividad = null;
	protected ?string $descripcion = null;
	protected float $tiempo_registrado = 0.0;
	protected string $fecha_registro = '';
	protected string $created_at = '';
	protected string $updated_at = '';

	public function __construct() {
		$this->addType('id_reporte', 'integer');
		$this->addType('id_empleado', 'integer');
		$this->addType('id_cliente', 'integer');
		$this->addType('id_actividad', 'integer');
		$this->addType('descripcion', 'string');
		$this->addType('tiempo_registrado', 'float');
		$this->addType('fecha_registro', 'string');
		$this->addType('created_at', 'string');
		$this->addType('updated_at', 'string');
	}

	public function read(): array {
		return [
			'id_reporte'        => $this->id_reporte,
			'id_cliente'        => $this->id_cliente,
			'id_actividad'      => $this->id_actividad,
			'id_empleado'       => $this->id_empleado,
			'descripcion'       => $this->descripcion,
			'tiempo_registrado' => $this->tiempo_registrado,
			'fecha_registro'    => $this->fecha_registro,
			'created_at'        => $this->created_at,
			'updated_at'        => $this->updated_at,
		];
	}
}
