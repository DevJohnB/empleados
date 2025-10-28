<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;

class clientes extends Entity {

	protected string $id_cliente   = '';
	protected string $nombre       = '';
	protected ?string $detalles    = null;
	protected ?string $cliente_padre = null;

	public function __construct() {
		// Tipos para (de)serialización
		$this->addType('id_cliente', 'integer');
		$this->addType('nombre', 'string');
		$this->addType('detalles', 'string');
		$this->addType('cliente_padre', 'integer');
	}

	public function read(): array {
		return [
			'Id_cliente'    => $this->id_cliente,
			'Nombre'        => $this->nombre,
			'Detalles'      => $this->detalles,
			'Cliente_padre' => $this->cliente_padre,
		];
	}
}
