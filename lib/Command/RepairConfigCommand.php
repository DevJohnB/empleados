<?php
declare(strict_types=1);

namespace OCA\Empleados\Command;

use OCP\IDBConnection;
use OCP\DB\QueryBuilder\IQueryBuilder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepairConfigCommand extends Command {
    protected static $defaultName = 'empleados:repair-config';

    public function __construct(private IDBConnection $db) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setDescription('Ensure required keys exist in empleados_conf with Data = NULL (idempotent).');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $keys = [
            'usuario_almacenamiento',
            'automatic_save_note',
            'acumular_vacaciones',
            'modulo_ahorro',
            'modulo_ausencias',
            'ausencias_readonly',
        ];

        $table = 'empleados_conf'; // sin prefijo manual

        $this->db->beginTransaction();
        try {
            foreach ($keys as $k) {
                // ¿Existe la fila?
                $qb = $this->db->getQueryBuilder();
                $qb->select('*')
                   ->from($table)
                   ->where($qb->expr()->eq('Nombre', $qb->createNamedParameter($k)))
                   ->setMaxResults(1);

                $exists = $qb->executeQuery()->fetchOne(); // string|int|false|null

                if ($exists === false || $exists === null) {
                    // INSERT con Data = NULL
                    $ib = $this->db->getQueryBuilder();
                    $ib->insert($table)
                       ->values([
                           'Nombre' => $ib->createNamedParameter($k),
                           'Data'   => $ib->createNamedParameter(null, IQueryBuilder::PARAM_NULL),
                       ])
                       ->executeStatement();
                    $output->writeln("INSERT: $k -> NULL");
                } else {
                    // UPDATE a NULL (idempotente)
                    $ub = $this->db->getQueryBuilder();
                    $ub->update($table)
                       ->set('Data', $ub->createNamedParameter(null, IQueryBuilder::PARAM_NULL))
                       ->where($ub->expr()->eq('Nombre', $ub->createNamedParameter($k)))
                       ->executeStatement();
                    $output->writeln("UPDATE: $k -> NULL");
                }
            }

            $this->db->commit();
            $output->writeln('Done.');
            return Command::SUCCESS;

        } catch (\Throwable $e) {
            $this->db->rollBack();
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
