<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use DateTime;
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
use OCA\Empleados\Db\reportetiempoMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\reportetiempo;
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
 * Controlador para la gestión de reporte de empleados en Nextcloud.
 */
class reportetiempoController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $reportetiempoMapper;
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
        reportetiempoMapper $reportetiempoMapper,
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
        $this->reportetiempoMapper = $reportetiempoMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;
        $this->groupManager = $groupManager;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->clientService = $clientService;
        $this->subAdmin = $subAdmin;
    }

    /**
     * Obtiene la lista de reportetiempo.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetReportes(): DataResponse {
        $this->checkAccess(['admin', 'empleados', 'recursos_humanos']);
        return new DataResponse($this->reportetiempoMapper->findAll(), Http::STATUS_OK);
    }

    /**
     * Obtiene la lista de reportetiempo por id.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function findById(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos', 'empleados']);
        $empleado = $this->empleadosMapper->GetMyEmployeeInfo($this->userSession->getUser()->getUID());

        return new DataResponse($this->reportetiempoMapper->findById($empleado[0]['Id_empleados']), Http::STATUS_OK);
    }

    /**
     * eliminar reportetiempo.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function deleteReport($id): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos', 'empleados']);
        return new DataResponse($this->reportetiempoMapper->deleteById($id), Http::STATUS_OK);
    }

    /**
     * Guarda cambios en los reportetiempo.
     */
    #[UseSession]
    #[NoAdminRequired] // si aplica, cámbiala por #[AdminRequired]
    public function modificarReporte(int $id_reporte, $id_actividad, $tiemporegistrado, $descripcion, string $tipo, $fecharegistrada): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos', 'empleados']);
        $empleado = $this->empleadosMapper->GetMyEmployeeInfo($this->userSession->getUser()->getUID());

        $tipo = strtolower(trim($tipo));
        if ($tipo === 'horas') {
            $tiemporegistrado *= 60; // <-- usa la MISMA variable
        }

        $fecha = (new \DateTimeImmutable($fecharegistrada))->format('Y-m-d');

        $this->reportetiempoMapper->updateReporte($id_reporte, $id_actividad, $empleado[0]['Id_empleados'], $descripcion, $tiemporegistrado, $fecha);
        return new DataResponse('ok', Http::STATUS_OK);
    }

        /**
         * Crea un nuevo reportetiempo.
         */
        #[UseSession]
        #[NoAdminRequired]
        public function crearReporte($id_cliente, $id_actividad, $tiemporegistrado, $descripcion, string $tipo, $time): DataResponse {
            $this->checkAccess(['admin', 'recursos_humanos', 'empleados']);

            $empleado = $this->empleadosMapper->GetMyEmployeeInfo($this->userSession->getUser()->getUID());
            
            $fecha = (new \DateTimeImmutable($time))->format('Y-m-d');

            $tipo = strtolower(trim($tipo));
            if ($tipo === 'horas') {
                $tiemporegistrado *= 60; // <-- usa la MISMA variable
            }
            
            $reportetiempo = new reportetiempo();
            $reportetiempo->setid_empleado($empleado[0]['Id_empleados'] );
            $reportetiempo->setid_cliente($id_cliente);
            $reportetiempo->setid_actividad($id_actividad);
            $reportetiempo->settiempo_registrado($tiemporegistrado);
            $reportetiempo->setfecha_registro($fecha);
            $reportetiempo->setdescripcion($descripcion);
            $this->reportetiempoMapper->insert($reportetiempo);

            return new DataResponse(Http::STATUS_OK);
        }

    /**
     * Obtiene la lista de empleados con validación de acceso.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetEmpleadosReports($periodo_inicio = null, $periodo_fin = null, $anio = null): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos', 'empleados']);

        $user = $this->userSession->getUser();
        $equipo_empleado = $this->empleadosMapper->GetSubordinates($user->getUID());

        $empleados_data = [];

        foreach ($equipo_empleado as $empleado) {
            $total = 0.0;

            $ausencias = $this->reportetiempoMapper->findById((int)$empleado['Id_empleados'], 0, 0, $periodo_fin, $periodo_inicio, $anio);

            foreach ($ausencias as $item) {
                $total += (float)$item['tiempo_registrado'];
            }

            // agrega el total al array del empleado con una clave
            $empleado['total_tiempo_registrado'] = $total;

            $empleados_data[] = $empleado;
        }

        return new DataResponse($empleados_data, Http::STATUS_OK);
    }


    /**
     * Exporta la lista de reportetiempo a un archivo XLSX.
     */
    public function ExportarReportes(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $reportetiempo = $this->reportetiempoMapper->findAll();
        $books = [['id_reportetiempo', 'nombre', 'detalles', 'tiempo_estimado', 'tiempo_real']];

        foreach ($reportetiempo as $reportetiempo) {
            $books[] = [
                $reportetiempo['id_reportetiempo'],
                $reportetiempo['nombre'],
                $reportetiempo['tiempo_estimado'],
                $reportetiempo['tiempo_real'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('reportetiempotiempo.xlsx');
        return new DataResponse($books, Http::STATUS_OK);
    }

    /**
     * Importa la lista de reportetiempo desde un archivo XLSX.
     */
    public function ImportarReportes(): DataResponse {
        $file = $this->getUploadedFile('ReportesfileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->reportetiempoMapper->updateReporte((int) $row[0], (string) $row[1], (string) $row[2], (float) $row[3]);
                } else {
                    $reportetiempo = new reportetiempo();
                    $reportetiempo->setnombre($row[1]);
                    $reportetiempo->setdetalles($row[2]);
                    $reportetiempo->settiempo_estimado($row[3]);
                    $this->reportetiempoMapper->insert($reportetiempo);
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
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new UploadException($this->l10n->t('Error en la subida del archivo.'));
        }
        return $file;
    }
}
