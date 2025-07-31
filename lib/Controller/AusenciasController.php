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
use OCA\Empleados\UploadException;
use OCP\AppFramework\Http\DataResponse;

use OCA\Empleados\Db\configuracionesMapper;
use OCP\Files\IRootFolder;
use OCP\IUserManager;

use OCP\IUserSession;

use DateTime;

use OCA\Empleados\Db\equiposMapper;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\ausenciasMapper;
use OCA\Empleados\Db\tipoausenciaMapper;
use OCA\Empleados\Db\historialausenciasMapper;
use OCA\Empleados\Db\ausencias;

use OCP\IURLGenerator;
use OCP\IGroupManager;
use OCP\Activity\IManager;

use OCA\Empleados\Helper\MailHelper;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class AusenciasController extends Controller {

    protected $userSession;
    protected $l10n;
    protected $equiposMapper;
    protected $empleadosMapper;
    protected $ausenciasMapper;
    protected $tipoausenciaMapper;
    protected $historialausenciasMapper;
    protected $configuracionesMapper;

    protected $userManager;

    protected IRootFolder $rootFolder;

    private IManager $activityManager;
	private IURLGenerator $urlGenerator;
    private MailHelper $mailHelper;

    public function __construct(
        IRequest $request,
        IL10N $l10n,
        ausenciasMapper $ausenciasMapper,
        equiposMapper $equiposMapper,
        historialausenciasMapper $historialausenciasMapper,
        tipoausenciaMapper $tipoausenciaMapper,
        empleadosMapper $empleadosMapper,
        IUserSession $userSession,
        configuracionesMapper $configuracionesMapper,
        IRootFolder $rootFolder,
        IUserManager $userManager,
        IGroupManager $groupManager,
        IManager $activityManager,
		IURLGenerator $urlGenerator,
        MailHelper $mailHelper
    ) {
        parent::__construct(Application::APP_ID, $request, $userSession, $configuracionesMapper, $groupManager);
        
        $this->l10n = $l10n;
        $this->empleadosMapper = $empleadosMapper;
        $this->ausenciasMapper = $ausenciasMapper;
        $this->equiposMapper = $equiposMapper;
        $this->tipoausenciaMapper = $tipoausenciaMapper;
        $this->historialausenciasMapper = $historialausenciasMapper;
        $this->userSession = $userSession;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->rootFolder = $rootFolder;
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
        $this->activityManager = $activityManager;
		$this->urlGenerator = $urlGenerator;
        $this->mailHelper = $mailHelper;
    }
    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    private function registrarActividadAusencia(string $tipoAusencia, string $fechaInicio, string $fechaFin, ?int $idHistorialAusencia): void {
        $user = $this->userSession->getUser()->getUID();
        $username = $this->userSession->getUser()->getDisplayName();
        $employe_info = $this->empleadosMapper->GetMyEmployeeInfo($user);

        $event = $this->activityManager->generateEvent();
        $event->setApp('empleados');
        $event->setType('empleados');
        $event->setObject('empleados', (int) $idHistorialAusencia, 'Solicitud de ausencia');
        $event->setAffectedUser($user);

        // ✅ Usa el subject ID y pasa los parámetros de forma estándar
        $event->setSubject(
            'ausencia_registrada',
            [
                'nombre' => (string) $user,
                'tipo_ausencia' => (string) $tipoAusencia
            ]
        );

        // ✅ Usa el message como resumen textual
        $event->setMessage('Desde "' . $fechaInicio . '" hasta "' . $fechaFin . '"');
        $this->activityManager->publish($event);
 
        foreach ([$employe_info[0]['Id_gerente'], $employe_info[0]['Id_socio'], $this->configuracionesMapper->GetGestor()[0]['Data']] as $usuario) {
            $userM = $this->userManager->get($usuario);
            $mail = $userM->getEMailAddress();
            
            $dependent = $this->empleadosMapper->GetMyEmployeeInfo($user);

            $event = $this->activityManager->generateEvent();
            $event->setApp('empleados');
            $event->setType('empleados');
            $event->setObject('empleados', (int) $idHistorialAusencia, 'Solicitud de ausencia');
            $event->setAffectedUser($usuario);

            // ✅ Usa el subject ID y pasa los parámetros de forma estándar
            $event->setSubject(
                'ausencia_registrada',
                [
                    'nombre' => (string) $user,
                    'tipo_ausencia' => (string) $tipoAusencia
                ]
            );

            // ✅ Usa el message como resumen textual
            $event->setMessage('Desde "' . $fechaInicio . '" hasta "' . $fechaFin . '"');
            $this->activityManager->publish($event);

            $this->mailHelper->enviarCorreo(
                $mail,
                'Nueva solicitud',
                [
                    'Hola ' . $userM->getDisplayName() . '',
                    'El usuario ' . $username . ' ha realizado una solicitud de "' . $tipoAusencia . '".',
                    'Fecha de inicio: ' . $fechaFin . '  - Fecha de finalización: ' . $fechaFin . '',
                    '',
                ]
            );
        }
    }
    
    
    #[UseSession]
    #[NoAdminRequired]
    public function GetNotificationsSubordinates(): array {
        $user = $this->userSession->getUser();
        $equipo_empleado = $this->empleadosMapper->GetSubordinates($user->getUID());

        $empleados_data = [];
        foreach($equipo_empleado as $empleado) {
            $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($empleado['Id_user']);
            $ausencias = $this->ausenciasMapper->GetAusenciasByUser($id_empleado[0]['Id_empleados']);
            
            if ($id_empleado[0]['Id_gerente'] == $user->getUID()){
                $ausencias_historial_gerente = $this->historialausenciasMapper->GetAusenciasHistorialGerente($ausencias[0]['id_ausencias']);
            }
            else if ($id_empleado[0]['Id_socio'] == $user->getUID()){
                $ausencias_historial_socio = $this->historialausenciasMapper->GetAusenciasHistorialSocio($ausencias[0]['id_ausencias']);
            }

            if (isset($ausencias_historial_gerente)) {
                foreach ($ausencias_historial_gerente as $item) {
                    $empleados_data[] = $item;
                }
                unset($ausencias_historial_gerente);
            }
            if (isset($ausencias_historial_socio)) {
                foreach ($ausencias_historial_socio as $item) {
                    $empleados_data[] = $item;
                }
                unset($ausencias_historial_socio);
            }
        }
        
        return $empleados_data;
    }

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusencias(): array {
        return $this->ausenciasMapper->Getausencias();
    }

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasByUser($id): array {
        return $this->ausenciasMapper->GetAusenciasByUser($id);
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportListAusencias(): array {
        $ausencias = $this->ausenciasMapper->Getausencias();
        $books = [['id_universario', 'numero_ausencias', 'dias']];

        foreach ($ausencias as $area) {
            $books[] = [
                $area['id_ausencias'],
                $area['numero_ausencias'],
                $area['dias'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('ausencias.xlsx');
        return $books;
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function ImportListAusencias(): void {
        $file = $this->getUploadedFile('fileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->ausenciasMapper->updateAusencias((int) $row[0], (int) $row[1], (float) $row[2]);
                } else {
                    $area = new ausencias();
                    $area->setnumero_ausencias($row[1]);
                    $area->setdias($row[2]);
                    $this->ausenciasMapper->insert($area);
                }
            }
        }
    }
        
    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function VaciarAusencias(): string {
        try {
            $this->ausenciasMapper->VaciarAusencias();
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function EliminarArea(int $id_departamento): string {
        try {
            $this->departamentosMapper->EliminarArea((string) $id_departamento);
            return "ok";
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    /**
     * Guarda cambios en las áreas.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GuardarCambioArea(int $id_departamento, string $padre, string $nombre): void {
        $this->departamentosMapper->updateAusencias((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function AgregarNuevoAniversario(int $numero_ausencias, string $fecha_de, string $fecha_hasta, float $dias): void {
        $area = new ausencias();
        $area->setnumero_ausencias($numero_ausencias);
        $area->setfecha_de($fecha_de);
        $area->setfecha_hasta($fecha_hasta);
        $area->setdias($dias);
        $this->ausenciasMapper->insert($area);
    }

    /**
     * Obtiene un archivo subido y maneja posibles errores.
     */
    #[UseSession]
    #[NoAdminRequired]
    private function getUploadedFile(string $key): array {
        $file = $this->request->getUploadedFile($key);
        if (empty($file) || ($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
            throw new UploadException($this->l10n->t('Error en la subida del archivo.'));
        }
        return $file;
    }

    /**
     * Obtiene la lista de ausencias.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAniversarioByDate(string $ingreso): array {
        $fechaInicio = new DateTime($ingreso);
        $hoy = new DateTime();
    
        $diferencia = $hoy->diff($fechaInicio);
    
        return $this->ausenciasMapper->GetAniversarioByDate($diferencia->y);

    }

    /**
    * Generar solicitud de ausencia con sus respectivos archivos adjuntos.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function EnviarAusencia(): DataResponse {
        try {
            $files = $_FILES['archivos'] ?? ['name' => [], 'tmp_name' => []];
            $fileCount = count((array)($files['name'] ?? []));

        
            $user = $this->userSession->getUser();
            $gestor = $this->configuracionesMapper->GetGestor()[0]['Data'] ?? null;
        
            if (!$gestor) {
                throw new \Exception('No se encontró la carpeta del gestor de información.');
            }
        
            $userFolder = $this->rootFolder->getUserFolder($gestor);
            $folderPath = "EMPLEADOS/" . $user->getUID() . " - " . strtoupper($user->getDisplayName()) . "/JUSTIFICANTES";
        
            if (!$userFolder->nodeExists($folderPath)) {
                $userFolder->newFolder($folderPath);
            }
        
            $carpetaDestino = $userFolder->get($folderPath);
            $fechaActual = (new \DateTime())->format('Y-m-d');
        
            for ($i = 0; $i < $fileCount; $i++) {
                $tmpName = $files['tmp_name'][$i];
                $originalName = $files['name'][$i];
        
                if (is_uploaded_file($tmpName)) {
                    $content = file_get_contents($tmpName);
        
                    // Separar extensión
                    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
                    $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        
                    // Construir nuevo nombre
                    $newName = $fechaActual . '-' . $baseName . '.' . $extension;
        
                    if ($carpetaDestino->nodeExists($newName)) {
                        $carpetaDestino->get($newName)->putContent($content);
                    } else {
                        $carpetaDestino->newFile($newName)->putContent($content);
                    }
                }
            }
            
            $id_tipo_ausencia = $this->request->getParam('id_tipo_ausencia');
            $dias_solicitados = $this->request->getParam('dias_solicitados');
            $fecha_de = $this->request->getParam('fecha_de');
            $fecha_hasta = $this->request->getParam('fecha_hasta');
            $prima_vacacional = $this->request->getParam('prima_vacacional');
            $notas = $this->request->getParam('notas');

            // aqui se disminuyen los dias de la ausencia
            $tipo_ausencia = $this->tipoausenciaMapper->getTipoById($id_tipo_ausencia);
            $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());
            $empleado_ausencias = $this->ausenciasMapper->GetAusenciasByUser($id_empleado[0]['Id_empleados']);
            if (!empty($tipo_ausencia) && $tipo_ausencia[0]['solicitar_prima_vacacional'] == 1) {
                $dias_disponibles = $empleado_ausencias[0]['dias_disponibles'] - $dias_solicitados;
                $this->ausenciasMapper->updateAusenciasEmpleado($empleado_ausencias[0]['id_ausencias'], $dias_disponibles);
            }
            
            $fecha_de = DateTime::createFromFormat('d/m/Y', $this->request->getParam('fecha_de'))->format('Y-m-d');
            $fecha_hasta = DateTime::createFromFormat('d/m/Y', $this->request->getParam('fecha_hasta'))->format('Y-m-d');

            // Registro en el historial de ausencias 
            $idHistorialAusencia = $this->historialausenciasMapper->EnviarAusencia((int) $id_tipo_ausencia, $empleado_ausencias[0]['id_ausencias'], $fecha_de, $fecha_hasta, (int) $prima_vacacional, $notas, $empleado_ausencias[0]['id_aniversario']);

            $this->registrarActividadAusencia($tipo_ausencia[0]['nombre'], $fecha_de, $fecha_hasta, $idHistorialAusencia);

            return new DataResponse(['success' => true, 'message' => $tipo_ausencia[0]['solicitar_prima_vacacional']]);
        } catch (\Exception $e) {
            // Manejo de errores
            return new DataResponse(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
    * Obtener ausencias del historial de ausencias por mes y año.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasHistorial(): DataResponse {
        $user = $this->userSession->getUser();
        $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());
        $empleado_ausencias = $this->ausenciasMapper->GetAusenciasByUser($id_empleado[0]['Id_empleados']);

        $desde = $this->request->getParam('desde'); // formato ISO
        $hasta = $this->request->getParam('hasta');

        $response = $this->historialausenciasMapper->GetAusenciasEnRango(
            $desde,
            $hasta,
            $empleado_ausencias[0]['id_ausencias']
        );
        
        foreach ($response as &$a) {
                $a['nombre_empleado'] = $id_empleado[0]['Id_user']; // si existe
        }

        return new DataResponse(['success' => true, 'message' => $response]);
    }

    /**
    * Obtener ausencias del historial de ausencias por mes y año.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasHistorialAll(): DataResponse {
        $desde = $this->request->getParam('desde');
        $hasta = $this->request->getParam('hasta');

        $user = $this->userSession->getUser();
        $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());
        $equipo_empleado = $this->empleadosMapper->GetEmpleadosEquipo($id_empleado[0]['Id_equipo']);

        $response = [];

        foreach ($equipo_empleado as $empleado) {
            $empleado_inf = $this->ausenciasMapper->GetAusenciasByUser($empleado['Id_empleados']);
            $ausencias = $this->historialausenciasMapper->GetAusenciasEnRango(
                $desde,
                $hasta,
                $empleado_inf[0]['id_ausencias']
            );

            // opcional: agrega nombre del empleado a cada evento
            foreach ($ausencias as &$a) {
                $a['nombre_empleado'] = $empleado['Id_user']; // si existe
            }

            $response = array_merge($response, $ausencias);
        }

        return new DataResponse(['success' => true, 'message' => $response]);
    }

    /**
    * Obtener ausencias de mis empleados.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasMyWorkers(): DataResponse {
        $desde = $this->request->getParam('desde');
        $hasta = $this->request->getParam('hasta');

        $user = $this->userSession->getUser();
        $equipo_empleado = $this->empleadosMapper->GetSubordinates($user->getUID());

        $response = [];

        foreach ($equipo_empleado as $empleado) {
            $empleado_inf = $this->ausenciasMapper->GetAusenciasByUser($empleado['Id_empleados']);
            $ausencias = $this->historialausenciasMapper->GetAusenciasEnRango(
                $desde,
                $hasta,
                $empleado_inf[0]['id_ausencias']
            );

            // opcional: agrega nombre del empleado a cada evento
            foreach ($ausencias as &$a) {
                $a['nombre_empleado'] = $empleado['Id_user']; // si existe
            }

            $response = array_merge($response, $ausencias);
        }

        return new DataResponse(['success' => true, 'message' => $response]);
    }

    /**
    * Obtener ausencias del historial de ausencias por mes y año.
    */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAusenciasEmployeeHistorial(): DataResponse {
        $usuariosInput = $this->request->getParam('id_employee');
        $desde = $this->request->getParam('desde');
        $hasta = $this->request->getParam('hasta');

        $user = $this->userSession->getUser();
        $uid = $user->getUID();

        // Validar privilegios
        $isPrivileged = $this->groupManager->isInGroup($uid, 'admin') ||
                        $this->groupManager->isInGroup($uid, 'recursos_humanos');

        // Obtener IDs del equipo del usuario
        $id_empleado = $this->empleadosMapper->GetMyEmployeeInfo($uid);
        $equipo_empleado = $this->empleadosMapper->GetEmpleadosEquipo($id_empleado[0]['Id_equipo']);
        $ids_equipo = array_map(fn($e) => (int) $e['Id_empleados'], $equipo_empleado);

        // 🛡️ Normalizar entrada
        if (is_string($usuariosInput)) {
            $usuariosInput = json_decode($usuariosInput, true);
        }
        $usuarios = is_array($usuariosInput) ? (array_keys($usuariosInput) === range(0, count($usuariosInput) - 1) ? $usuariosInput : [$usuariosInput]) : [];

        $response = [];

        foreach ($usuarios as $item) {
            if (!is_array($item) || !isset($item['Id_empleados'])) {
                continue;
            }

            $id_emp = (int) $item['Id_empleados'];

            if ($isPrivileged || in_array($id_emp, $ids_equipo)) {
                $empleado_ausencias = $this->ausenciasMapper->GetAusenciasByUser($id_emp);
                if (empty($empleado_ausencias)) {
                    continue;
                }

                $ausencias = $this->historialausenciasMapper->GetAusenciasEnRango(
                    $desde,
                    $hasta,
                    $empleado_ausencias[0]['id_ausencias']
                );

                $nombre = $item['displayName'] ?? $item['Id_user'] ?? 'Empleado ' . $id_emp;

                foreach ($ausencias as &$a) {
                    $a['nombre_empleado'] = $nombre;
                }

                $response = array_merge($response, $ausencias);
            }
        }

        return new DataResponse(['success' => true, 'message' => $response]);
    }
}
