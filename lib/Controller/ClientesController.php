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
use OCA\Empleados\Db\clientesMapper;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleados;
use OCA\Empleados\Db\clientes;
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
 * Controlador para la gestión de clientes de empleados en Nextcloud.
 */
class ClientesController extends BaseController {

    protected $userSession;
    protected $userManager;
    protected $empleadosMapper;
    protected $clientesMapper;
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
        clientesMapper $clientesMapper,
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
        $this->clientesMapper = $clientesMapper;
        $this->configuracionesMapper = $configuracionesMapper;
        $this->l10n = $l10n;
        $this->groupManager = $groupManager;
        $this->config = $config;
        $this->urlGenerator = $urlGenerator;
        $this->clientService = $clientService;
        $this->subAdmin = $subAdmin;
    }

    /**
     * Obtiene la lista de clientes.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function GetCompaniesGroups(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->clientesMapper->findAll(), Http::STATUS_OK);
    }

    /**
     * Obtiene la lista de clientes por id.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function findById($id): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->clientesMapper->findById($id), Http::STATUS_OK);
    }

    /**
     * eliminar clientes.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function deleteById($id): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        return new DataResponse($this->clientesMapper->deleteById($id), Http::STATUS_OK);
    }

    /**
     * Guarda cambios en los clientes.
     */
    #[UseSession]
    #[NoAdminRequired] // si aplica, cámbiala por #[AdminRequired]
    public function modificarCliente(int $id_clientes, string $nombre, ?string $detalles, ?int $cliente_padre): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $this->clientesMapper->updateClientes($id_clientes, $nombre, $detalles, $cliente_padre);
        return new DataResponse('ok', Http::STATUS_OK);
    }

    /**
     * Crea un nuevo clientes.
     */
    #[UseSession]
    #[NoAdminRequired]
    public function crearCliente(string $nombre, ?string $detalles, ?int $cliente_padre): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        
        $clientes = new clientes();
        $clientes->setnombre($nombre);
        $clientes->setdetalles($detalles);
        $clientes->setcliente_padre($cliente_padre);
        $this->clientesMapper->insert($clientes);

        return new DataResponse(Http::STATUS_OK);
    }


    /**
     * Exporta la lista de clientes a un archivo XLSX.
     */
    public function ExportListclientes(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $clientes = $this->clientesMapper->GetclientesList();
        $books = [['Id_clientes', 'Nombre', 'created_at', 'updated_at']];

        foreach ($clientes as $clientes) {
            $books[] = [
                $clientes['Id_clientes'],
                $clientes['Nombre'],
                $clientes['created_at'],
                $clientes['updated_at'],
            ];
        }

        \Shuchkin\SimpleXLSXGen::fromArray($books)->downloadAs('clientes.xlsx');
        return new DataResponse($books, Http::STATUS_OK);
    }

    /**
     * Importa la lista de clientes desde un archivo XLSX.
     */
    public function ImportListclientes(): DataResponse {
        $this->checkAccess(['admin', 'recursos_humanos']);
        $file = $this->getUploadedFile('clientesfileXLSX');
        if ($xlsx = \Shuchkin\SimpleXLSX::parse($file['tmp_name'])) {
            foreach ($xlsx->rows() as $row) {
                if (!empty($row[0])) {
                    $this->clientesMapper->updateclientes((string) $row[0], (string) $row[1]);
                } else {
                    $timestamp = date('Y-m-d');
                    $clientes = new clientes();
                    $clientes->setnombre((string) $row[1]);
                    $clientes->setcreated_at($timestamp);
                    $clientes->setupdated_at($timestamp);
                    $this->clientesMapper->insert($clientes);
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
