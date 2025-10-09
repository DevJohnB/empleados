<?php
namespace OCA\Empleados\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\IConfig;
use OCP\IL10N;
use OCP\Settings\ISettings;
use OCA\Empleados\Db\configuracionesMapper;


class settingsAdmin implements ISettings {
    private IL10N $l;
    private IConfig $config;
	protected $configuracionesMapper;

    public function __construct(IConfig $config, IL10N $l, configuracionesMapper $configuracionesMapper) {
        $this->config = $config;
        $this->l = $l;

		$this->configuracionesMapper = $configuracionesMapper;
    }

    /**
     * @return TemplateResponse
     */
    public function getForm() {
        
        $rows = $this->configuracionesMapper->GetConfig();
        $params = is_array($rows) ? array_column($rows, 'Data', 'Nombre') : [];

        return new TemplateResponse('empleados', 'settings/admin', [
            'config' => $params,
        ], '');
    }

    public function getSection() {
        return 'empleados'; // Name of the previously created section.
    }

    public function getPriority() {
        return  1;
    }
}

