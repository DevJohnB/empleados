<?php

declare(strict_types=1);
namespace OCA\Empleados\Controller;

use OCP\AppFramework\Controller;
use OCP\AppFramework\OCS\OCSForbiddenException;
use OCP\IRequest;
use OCP\IUserSession;
use OCP\IGroupManager;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\configuracionesMapper;


abstract class BaseController extends Controller {

    protected $userSession;
    protected $groupManager;
    protected $configuracionesMapper;
    protected $empleadosMapper;
    protected $configParams = []; // 🔹 Aquí almacenaremos la configuración

    public function __construct(
        string $appName,
        IRequest $request,
        IUserSession $userSession,
        IGroupManager $groupManager,
        empleadosMapper $empleadosMapper,
        configuracionesMapper $configuracionesMapper // 🔹 Agregamos el Mapper de configuración
    ) {
        parent::__construct($appName, $request);
        $this->userSession = $userSession;
        $this->groupManager = $groupManager;
        $this->empleadosMapper = $empleadosMapper;
        $this->configuracionesMapper = $configuracionesMapper;

        $this->checkAccess(); // 🔥 Validar accesos automáticamente
        $this->loadConfigParams(); // 🔥 Cargar configuración automáticamente
    }

    private function checkAccess(): void {
        $allowedGroups = ['admin', 'empleados', 'recursos_humanos'];
        $user = $this->userSession->getUser();

        if (!$user) {
            throw new OCSForbiddenException("❌ Debes estar autenticado para acceder a este módulo.");
        }

        $userGroups = $this->groupManager->getUserGroups($user);
        if (!$userGroups || count($userGroups) === 0) {
            throw new OCSForbiddenException("⚠️ No perteneces a ningún grupo permitido para acceder.");
        }

        foreach ($userGroups as $group) {
            $groupId = $group->getGID();
            if ($groupId && in_array($groupId, $allowedGroups)) {
                return; // ✅ Acceso permitido
            }
        }

        throw new OCSForbiddenException("🚫 No tienes permiso para acceder a este módulo. Contacta al administrador.");
    }


    public function AdminCheckAccess($flag = true): void {
        if (!$flag) {
            $allowedGroups = ['admin', 'recursos_humanos'];
            $user = $this->userSession->getUser();

            if (!$user) {
                throw new OCSForbiddenException("❌ Debes estar autenticado para acceder a este módulo.");
            }

            $userGroups = $this->groupManager->getUserGroups($user);
            if (!$userGroups || count($userGroups) === 0) {
                throw new OCSForbiddenException("⚠️ No perteneces a ningún grupo permitido para acceder.");
            }

            foreach ($userGroups as $group) {
                $groupId = $group->getGID();
                if ($groupId && in_array($groupId, $allowedGroups)) {
                    return; // ✅ Acceso permitido
                }
            }

            throw new OCSForbiddenException("🚫 No tienes permiso para acceder a este módulo. Contacta al administrador.");
        }
    }

    
    public function GroupCheckAccess(): array {
        $user = $this->userSession->getUser();
        return $this->groupManager->getUserGroups($user);
    }

    /**
     * 🔥 Carga la configuración automáticamente para todos los controladores.
     */
    private function loadConfigParams(): void {
        $configMap = array_column($this->configuracionesMapper->GetConfig(), 'Data', 'Nombre');

        $this->configParams = [
            "usuario_almacenamiento" => $configMap['usuario_almacenamiento'] ?? null,
            "automatic_save_note" => $configMap['automatic_save_note'] ?? null,
            "acumular_vacaciones" => $configMap['acumular_vacaciones'] ?? null,
            "modulo_ahorro" => $configMap['modulo_ahorro'] ?? null,
            "modulo_ausencias" => $configMap['modulo_ausencias'] ?? null,
        ];
    }

    /**
     * 📌 Permite que los controladores accedan a la configuración cargada.
     */
    public function getConfigParams(): array {
        return $this->configParams;
    }

    /**
     * 📌 Permite que los controladores accedan los datos del empleado actual.
     */
    public function getEmployeeInfo(): array {
        $user = $this->userSession->getUser();
        return $this->empleadosMapper->GetMyEmployeeInfo($user->getUID());
    }

    /**
     * 📌 Permite busacr si el empleado actual cuenta tiene subordinados
     */
    public function GetSubordinates(): array {
        $user = $this->userSession->getUser();
        return $this->empleadosMapper->GetSubordinates($user->getUID());
    }
}
