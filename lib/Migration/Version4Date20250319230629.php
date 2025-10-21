<?php

declare(strict_types=1);

/**
 * SPDX-FileCopyrightText: 2025 Nextcloud GmbH and Nextcloud contributors
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

/**
 * Migración estable e idempotente.
 * NOTA: No se declaran DEFAULTs de timestamp en el Schema; se aplican vía reparador específico por motor.
 */
class Version4Date20250319230629 extends SimpleMigrationStep {

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin acciones previas
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 * @return null|ISchemaWrapper
	 */
	public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
		/** @var ISchemaWrapper $schema */
		$schema = $schemaClosure();

		/**
		 * Tabla: aniversarios
		 */
		if (!$schema->hasTable('aniversarios')) {
			$table = $schema->createTable('aniversarios');

			$table->addColumn('id_aniversario', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('numero_aniversario', 'integer', [
				'notnull' => true,
			]);

			$table->addColumn('fecha_de', 'datetime', [
				'notnull' => false,
			]);

			$table->addColumn('fecha_hasta', 'datetime', [
				'notnull' => false,
			]);

			$table->addColumn('dias', 'decimal', [
				'precision' => 5,
				'scale' => 2,
				'notnull' => true,
			]);

			// Sin DEFAULT aquí; se asegura por reparador
			$table->addColumn('timestamp', 'datetime', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id_aniversario']);
			$table->addIndex(['numero_aniversario'], 'aniv_idx_numero');
		}

		/**
		 * Tabla: tipo_ausencia
		 */
		if (!$schema->hasTable('tipo_ausencia')) {
			$table = $schema->createTable('tipo_ausencia');

			$table->addColumn('id_tipo_ausencia', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('nombre', 'string', [
				'length' => 255,
				'notnull' => true,
			]);

			$table->addColumn('descripcion', 'text', [
				'notnull' => false,
			]);

			$table->addColumn('solicitar_archivo', 'integer', [
				'notnull' => true,
			]);

			$table->addColumn('solicitar_prima_vacacional', 'integer', [
				'notnull' => true,
				'default' => 0,
			]);

			$table->setPrimaryKey(['id_tipo_ausencia']);
			$table->addIndex(['nombre'], 'tipo_idx_nombre');
		}

		/**
		 * Tabla: ausencias
		 */
		if (!$schema->hasTable('ausencias')) {
			$table = $schema->createTable('ausencias');

			$table->addColumn('id_ausencias', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('id_empleado', 'integer', [
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('id_aniversario', 'integer', [
				'unsigned' => true,
				'notnull' => false,
			]);

			$table->addColumn('dias_disponibles', 'decimal', [
				'precision' => 5,
				'scale' => 2,
				'notnull' => false,
				'default' => 0.00,
			]);

			$table->addColumn('prima_vacacional', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			// Sin DEFAULT aquí; se asegura por reparador
			$table->addColumn('timestamp', 'datetime', [
				'notnull' => true,
			]);

			$table->setPrimaryKey(['id_ausencias']);
			$table->addUniqueIndex(['id_empleado'], 'uniq_aus_empleado');
			$table->addIndex(['id_aniversario'], 'aus_idx_aniv');
		}

		/**
		 * Tabla: historial_ausencias
		 */
		if (!$schema->hasTable('historial_ausencias')) {
			$table = $schema->createTable('historial_ausencias');

			$table->addColumn('id_historial_ausencias', 'integer', [
				'autoincrement' => true,
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('id_ausencias', 'integer', [
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('id_aniversario', 'integer', [
				'unsigned' => true,
				'notnull' => false,
			]);

			$table->addColumn('id_tipo_ausencia', 'integer', [
				'unsigned' => true,
				'notnull' => true,
			]);

			$table->addColumn('fecha_de', 'datetime', [
				'notnull' => true,
			]);

			$table->addColumn('fecha_hasta', 'datetime', [
				'notnull' => true,
			]);

			$table->addColumn('prima_vacacional', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			$table->addColumn('archivo', 'string', [
				'length' => 255,
				'notnull' => false,
			]);

			// Sin DEFAULT aquí; se asegura por reparador
			$table->addColumn('timestamp', 'datetime', [
				'notnull' => true,
			]);

			$table->addColumn('a_socio', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			$table->addColumn('a_gerente', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			$table->addColumn('a_capital_humano', 'boolean', [
				'notnull' => false,
				'default' => false,
			]);

			$table->setPrimaryKey(['id_historial_ausencias']);
			$table->addIndex(['id_ausencias'], 'hist_idx_aus');
			$table->addIndex(['id_tipo_ausencia'], 'hist_idx_tipo');
			$table->addIndex(['id_aniversario'], 'hist_idx_aniv');
		}

		return $schema;
	}

	/**
	 * @param IOutput $output
	 * @param Closure(): ISchemaWrapper $schemaClosure
	 * @param array $options
	 */
	public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
		// sin seeds en esta versión
	}
}
