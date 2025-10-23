<?php

declare(strict_types=1);

namespace OCA\Empleados\Db;

use OCP\AppFramework\Db\Entity;

class Cliente extends Entity {

	protected string $id_cliente   = '';
	protected string $nombre       = '';
	protected ?string $detalles    = null;
	protected ?string $cliente_padre = null;
	protected string $created_at   = '';
	protected string $updated_at   = '';

	public function __construct() {
		// Tipos para (de)serialización
		$this->addType('id_cliente', 'integer');
		$this->addType('nombre', 'string');
		$this->addType('detalles', 'string');
		$this->addType('cliente_padre', 'integer');
		$this->addType('created_at', 'string');
		$this->addType('updated_at', 'string');
	}

	public function read(): array {
		return [
			'Id_cliente'    => $this->id_cliente,
			'Nombre'        => $this->nombre,
			'Detalles'      => $this->detalles,
			'Cliente_padre' => $this->cliente_padre,
			'created_at'    => $this->created_at,
			'updated_at'    => $this->updated_at,
		];
	}
}
