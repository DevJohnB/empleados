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
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;

use DateTime;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleadosMapper;
use OCA\Empleados\Db\aniversarioMapper;
use OCA\Empleados\Db\aniversario;

use OCP\IUserSession;
use OCP\IUserManager;
use OCP\IGroupManager;

require_once 'SimpleXLSXGen.php';
require_once 'SimpleXLSX.php';

/**
 * Controlador para la gestión de áreas en Nextcloud.
 */
class AniversariosController extends BaseController {

    protected $l10n;
    protected $aniversarioMapper;
    protected $userSession;
    protected $configuracionesMapper;
    protected $empleadosMapper;

    public function __construct(
        IRequest $request,
        IUserSession $userSession,
        IGroupManager $groupManager,
        empleadosMapper $empleadosMapper,
        configuracionesMapper $configuracionesMapper,
        aniversarioMapper $aniversarioMapper,
        IL10N $l10n
    ) {
        parent::__construct(Application::APP_ID, $request, $userSession, $groupManager, $empleadosMapper, $configuracionesMapper);

        $this->l10n = $l10n;
        $this->aniversarioMapper = $aniversarioMapper;
        $this->empleadosMapper = $empleadosMapper;
        $this->groupManager = $groupManager;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->userSession = $userSession;
    }

    /**
     * Obtiene la lista de aniversarios.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function Getaniversarios(): DataResponse {
        $this->checkAccess(['admin', 'empleados']);
        return new DataResponse($this->aniversarioMapper->GetAniversarios(), Http::STATUS_OK);
    }

    /**
     * Exporta la lista de áreas a un archivo XLSX.
     */
    public function ExportListAniversarios(): DataDownloadResponse {

        $aniversarios = $this->aniversarioMapper->GetAniversarios();
    
        $books = [['numero_aniversario', 'dias']];
    
        foreach ($aniversarios as $area) {
            $books[] = [
                $area['numero_aniversario'],
                $area['dias'],
            ];
        }
    
        $xlsx = \Shuchkin\SimpleXLSXGen::fromArray($books)->toString();
    
        return new DataDownloadResponse(
            $xlsx,
            'aniversarios.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        );
    }

    /**
     * Importa la lista de áreas desde un archivo XLSX.
     */
    public function ImportListAniversarios(): DataResponse {

        try {
    
            $file = $this->getUploadedFile('fileXLSX');
    
            if (!$file || !isset($file['tmp_name'])) {
                return new DataResponse([
                    'success' => false,
                    'error' => 'Archivo no recibido'
                ], 400);
            }
    
            if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
    
                foreach ($xlsx->rows() as $row) {
    
                    $area = new aniversario();
                    $area->setnumero_aniversario((int)$row[0]);
                    $area->setdias((float)$row[1]);
    
                    $this->aniversarioMapper->insert($area);
                }
    
                return new DataResponse([
                    'success' => true,
                    'message' => 'Importación completada'
                ]);
            }
    
            return new DataResponse([
                'success' => false,
                'error' => 'No se pudo leer el archivo XLSX'
            ], 400);
    
        } catch (\Exception $e) {
    
            return new DataResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
        
    /**
     * Elimina un área por ID.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function VaciarAniversarios(): DataResponse {

        try {
    
            $this->aniversarioMapper->VaciarAniversarios();
    
            return new DataResponse([
                'success' => true,
                'message' => 'Aniversarios eliminados correctamente'
            ]);
    
        } catch (\Exception $e) {
    
            return new DataResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
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
        $this->departamentosMapper->updateAniversarios((string) $id_departamento, $padre, $nombre);
    }

    /**
     * Crea una nueva área.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function AgregarNuevoAniversario( int $numero_aniversario, string $fecha_de, string $fecha_hasta, float $dias): DataResponse {
        try {
    
            $area = new aniversario();
            $area->setnumero_aniversario($numero_aniversario);
            $area->setfecha_de($fecha_de);
            $area->setfecha_hasta($fecha_hasta);
            $area->setdias($dias);
    
            $this->aniversarioMapper->insert($area);
    
            return new DataResponse([
                'success' => true,
                'message' => 'Aniversario agregado correctamente'
            ]);
    
        } catch (\Exception $e) {
    
            return new DataResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
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
     * Obtiene la lista de aniversarios.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetAniversarioByDate(string $ingreso): DataResponse {

        $fechaInicio = new \DateTime($ingreso);
        $hoy = new \DateTime();
    
        $diferencia = $hoy->diff($fechaInicio);
    
        $resultado = $this->aniversarioMapper->GetAniversarioByDate($diferencia->y);
    
        return new DataResponse($resultado);
    }
}
