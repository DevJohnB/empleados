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
     * Obtiene la lista de actividad en formato clave-valor.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetactividadFix(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $response = array_map(fn($actividad) => [
            'value' => $actividad['Id_actividad'],
            'label' => $actividad['Nombre'],
        ], $this->actividadMapper->GetactividadList());

        return new DataResponse($response, Http::STATUS_OK);
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
     * Obtiene jefe de actividad
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetactividadJefe(): DataResponse {
        $this->checkAccess(['admin', 'empleados']);
        $id = $this->request->getParam('id');
        return new DataResponse($this->actividadMapper->GetactividadJefe((string)$id), Http::STATUS_OK);
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
     * Elimina un actividad por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function Eliminaractividad(int $id_actividad): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        try {
            $row = $this->actividadMapper->deleteByIdReturningRow((string)$id_actividad);
            if ($row) {
                // ajusta el nombre de la columna al real en tu tabla
                $nombreGrupo = $row['nombre_actividad'] ?? $row['Nombre'] ?? null;
                if ($nombreGrupo) {
                    $group = $this->groupManager->get($nombreGrupo);
                    if ($group) {
                        $group->delete($group);
                    }
                }
            } else {
                return new DataResponse('actividad_no_encontrado', Http::STATUS_NOT_FOUND);
            }
            return new DataResponse('ok', Http::STATUS_OK);
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }


    /**
     * Guarda cambios en los actividad.
     */
    #[UseSession]
    #[NoAdminRequired] // si aplica, cámbiala por #[AdminRequired]
    public function GuardarCambioactividad(int $Id_actividad, string $Id_jefe_actividad): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        // 1) leer estado actual
        $old = $this->actividadMapper->getById((string)$Id_actividad);
        if (!$old) throw new \RuntimeException("actividad $Id_actividad no existe");

        $groupName = $old['Nombre'] ?? $old['nombre'] ?? null;
        if (!$groupName) throw new \RuntimeException("actividad sin nombre");

        $oldJefe = $old['Id_jefe_actividad'] ?? $old['id_jefe_actividad'] ?? null;

        // 2) actualizar solo el jefe en BD
        $this->actividadMapper->updateactividad((string)$Id_actividad, $Id_jefe_actividad);

        // 3) asegurar grupo
        $group = $this->groupManager->get($groupName);
        if (!$group) {
            $this->groupManager->createGroup($groupName);
            $group = $this->groupManager->get($groupName);
            if (!$group) throw new \RuntimeException("No se pudo crear/obtener el grupo '$groupName'");
        }

        // 4) nuevo jefe
        $newBoss = $this->userManager->get($Id_jefe_actividad);
        if (!$newBoss) throw new \RuntimeException("Usuario '$Id_jefe_actividad' no existe");

        if (!$group->inGroup($newBoss)) $group->addUser($newBoss);

        // 5) promover nuevo jefe
        $this->subAdmin->createSubAdmin($newBoss, $group);

        // 6) quitar subadmin anterior (si cambió)
        if ($oldJefe && $oldJefe !== $Id_jefe_actividad) {
            $oldUser = $this->userManager->get($oldJefe);
            if ($oldUser) $this->subAdmin->deleteSubAdmin($oldUser, $group);
            // opcional: también puedes removerlo del grupo:
            // if ($oldUser && $group->inGroup($oldUser)) $group->removeUser($oldUser);
        }
        return new DataResponse('ok', Http::STATUS_OK);
    }

    /**
     * Crea un nuevo actividad.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearActividad(string $nombre, string $detalles, float $tiempoestimado, string $tipo): DataResponse {
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

    #[UseSession]
    #[NoAdminRequired]
    public function promoverJefeDeactividad(string $uid, string $gid): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        try {
            $user  = $this->userManager->get($uid);
            $group = $this->groupManager->get($gid);
            if (!$user || !$group) {
                return DataResponse('error: usuario o grupo no existen', Http::STATUS_BAD_REQUEST);
            }

            // Asegurar pertenencia al grupo (evita fallo de subadmin si no es miembro)
            if (!$group->inGroup($user)) {
                $group->addUser($user);
            }

            // Promover a subadmin SIN usar HTTP:
            $this->subAdmin->createSubAdmin($user, $group);

            return new DataResponse('ok', Http::STATUS_OK);
        } catch (\Throwable $e) {
            return new DataResponse('error: ' . $e->getMessage(), Http::STATUS_INTERNAL_SERVER_ERROR);
        }
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
