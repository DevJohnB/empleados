<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCA\Empleados\AppInfo\Application;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\Attribute\UseSession;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\IRequest;
use OCP\IL10N;
use OCP\IUserSession;
use OCP\IUserManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\actividadMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\actividad;
use OCA\Empleados\Db\configuraciones;
use OCA\Empleados\UploadException;
use OCP\IGroupManager;
use OCP\IConfig;

use OCP\IURLGenerator;
use OCP\Http\Client\IClientService;
use OCP\Group\ISubAdmin;

use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de actividad de empleados en Nextcloud.
 */
class actividadesController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $actividadMapper;
    protected $configuracionesMapper;
    protected $l10n;
    protected $groupManager;
    private IConfig $config;
    private IClientService $clientService;
    private ISubAdmin $subAdmin;   

    private IURLGenerator $urlGenerator;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        IUserManager $userManager,
        empleadosMapper $empleadosMapper,
        actividadMapper $actividadMapper,
        configuracionesMapper $configuracionesMapper,
        IL10N $l10n,
        IConfig $config,
		IGroupManager $groupManager,
        IURLGenerator $urlGenerator,
        IClientService $clientService,
        ISubAdmin $subAdmin,
    ) {
		parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $empleadosMapper, $configuracionesMapper);

        $this->userSession = $userSession;
        $this->userManager = $userManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->actividadMapper = $actividadMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;
        $this->groupManager = $groupManager;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->clientService = $clientService;
        $this->subAdmin = $subAdmin;
    }

    /**
     * Obtiene la lista de actividad.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetActividades(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->actividadMapper->findAll(), Http::STATUS_OK);
    }

    /**
     * Obtiene la lista de actividad por id.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function findById($id): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->actividadMapper->findById($id), Http::STATUS_OK);
    }

    /**
     * eliminar actividad.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function deleteById($id): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->actividadMapper->deleteById($id), Http::STATUS_OK);
    }

    /**
     * Guarda cambios en los actividad.
     */
    #[UseSession]
    #[NoAdminRequired] // si aplica, cámbiala por #[AdminRequired]
    public function modificarActividad(int $id_actividad, string $nombre, string $detalles, float $tiempoestimado, string $tipo): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $tipo = strtolower(trim($tipo));
        if ($tipo === 'horas') {
            $tiempoestimado *= 60; // <-- usa la MISMA variable
        }
        $this->actividadMapper->updateActividad($id_actividad, $nombre, $detalles, $tiempoestimado);
        return new DataResponse('ok', Http::STATUS_OK);
    }

    /**
     * Crea un nuevo actividad.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearActividad(string $nombre, ?string $detalles, float $tiempoestimado, string $tipo): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);

        $tipo = strtolower(trim($tipo));
        if ($tipo === 'horas') {
            $tiempoestimado *= 60; // <-- usa la MISMA variable
        }
        
        $actividad = new actividad();
        $actividad->setnombre($nombre);
        $actividad->setdetalles($detalles);
        $actividad->settiempo_estimado($tiempoestimado);
        $this->actividadMapper->insert($actividad);

        return new DataResponse(Http::STATUS_OK);
    }


    /**
     * Exporta la lista de actividad a un archivo XLSX.
     */
    public function ExportListactividad(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $actividad = $this->actividadMapper->GetactividadList();
        $books = [['Id_actividad', 'Nombre', 'created_at', 'updated_at']];

        foreach ($actividad as $actividad) {
            $books[] = [
                $actividad['Id_actividad'],
                $actividad['Nombre'],
                $actividad['created_at'],
                $actividad['updated_at'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('actividad.xlsx');
        return new DataResponse($books, Http::STATUS_OK);
    }

    /**
     * Importa la lista de actividad desde un archivo XLSX.
     */
    public function ImportListactividad(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $file = $this->getUploadedFile('actividadfileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->actividadMapper->updateactividad((string) $row[0], (string) $row[1]);
                } else {
                    $timestamp = date('Y-m-d');
                    $actividad = new actividad();
                    $actividad->setnombre((string) $row[1]);
                    $actividad->setcreated_at($timestamp);
                    $actividad->setupdated_at($timestamp);
                    $this->actividadMapper->insert($actividad);
                }
            }
        return new DataResponse(['status' => 'error'], Http::STATUS_BAD_REQUEST);
        }
        return new DataResponse(Http::STATUS_OK);
    }
    /**
     * Obtiene un archivo subido y maneja posibles errores.
     */
    private function getUploadedFile(string $key): array {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new UploadException($this->l10n->t('Error en la subida del archivo.'));
        }
        return $file;
    }
}
