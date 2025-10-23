<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;

class Actividad extends Entity {

	protected string $id_actividad   = '';
	protected string $nombre         = '';
	protected ?string $detalles      = null;
	protected ?string $tiempo_estimado = null; // horas decimales
	protected ?string $tiempo_real     = null; // horas decimales

	public function __construct() {
		$this->addType('id_actividad', 'integer');
		$this->addType('nombre', 'string');
		$this->addType('detalles', 'string');
		$this->addType('tiempo_estimado', 'float');
		$this->addType('tiempo_real', 'float');
	}

	public function read(): array {
		return [
			'Id_actividad'   => $this->id_actividad,
			'Nombre'         => $this->nombre,
			'Detalles'       => $this->detalles,
			'Tiempo_estimado'=> $this->tiempo_estimado,
			'Tiempo_real'    => $this->tiempo_real,
		];
	}
}
