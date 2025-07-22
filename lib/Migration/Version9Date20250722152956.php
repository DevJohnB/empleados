<?php

declare(strict_types=1);

namespace OCA\Empleados\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version9Date20250722152956 extends SimpleMigrationStep {

    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
    }

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('empleados')) {
            $table = $schema->getTable('empleados');

            // Agregar índice a Id_gerente si no existe
            if (!$table->hasIndex('idx_id_gerente')) {
                $table->addIndex(['Id_gerente'], 'idx_id_gerente');
            }

            // Agregar índice a Id_socio si no existe
            if (!$table->hasIndex('idx_id_socio')) {
                $table->addIndex(['Id_socio'], 'idx_id_socio');
            }
        }

        return $schema;
    }

    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
    }
}
