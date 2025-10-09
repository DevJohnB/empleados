<?php
namespace OCA\Empleados\Command;

use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\equiposMapper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RepairConfigCommand.php extends Command {
    protected static $defaultName = 'empleados:repair-tables';

    private equiposMapper $equiposMapper;
    private configuracionesMapper $configMapper;

    public function __construct(equiposMapper $equiposMapper, configuracionesMapper $configMapper) {
        parent::__construct();
        $this->equiposMapper = $equiposMapper;
        $this->configMapper = $configMapper;
    }

    protected function configure(): void {
        $this->setDescription('Run repair / seed logic for empleados tables (idempotent)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $output->writeln('Starting empleados repair...');

        // ejemplo: reparar tabla equipos
        try {
            if (method_exists($this->equiposMapper, 'repairEquiposTable')) {
                $this->equiposMapper->repairEquiposTable();
                $output->writeln('equipos table repaired.');
            } else {
                $output->writeln('equiposMapper::repairEquiposTable() not found — skipping.');
            }

            // ejemplo: seed configuraciones
            if (method_exists($this->configMapper, 'seedDefaults')) {
                $defaults = [
                    'provisioning_admin_user' => 'provisioner',
                    'provisioning_admin_pass' => '',
                    'acumular_vacaciones' => 'false',
                ];
                $this->configMapper->seedDefaults($defaults);
                $output->writeln('config defaults seeded.');
            } else {
                $output->writeln('configuracionesMapper::seedDefaults() not found — skipping.');
            }

            $output->writeln('Done.');
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Error: '.$e->getMessage().'</error>');
            return Command::FAILURE;
        }
    }
}
