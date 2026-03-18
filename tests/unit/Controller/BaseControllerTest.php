<?php

declare(strict_types=1);

namespace Controller;

use OCA\Empleados\Controller\BaseController;
use OCA\Empleados\Db\configuracionesMapper;
use OCA\Empleados\Db\empleadosMapper;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\ForbiddenException;
use OCP\IGroup;
use OCP\IGroupManager;
use OCP\IRequest;
use OCP\IUser;
use OCP\IUserSession;
use PHPUnit\Framework\TestCase;

class BaseControllerTest extends TestCase {
	public function testBaseControllerNowUsesRegularControllerBaseClass(): void {
		$this->assertTrue(is_subclass_of(BaseController::class, Controller::class));
	}

	public function testCheckAccessThrowsForbiddenExceptionWhenUserIsMissing(): void {
		$controller = $this->buildController(null, []);

		$this->expectException(ForbiddenException::class);
		$controller->checkAccess(['admin']);
	}

	public function testCheckAccessThrowsForbiddenExceptionWhenUserLacksRequiredGroup(): void {
		$user = $this->createMock(IUser::class);
		$controller = $this->buildController($user, ['empleados']);

		$this->expectException(ForbiddenException::class);
		$controller->checkAccess(['admin']);
	}

	public function testCheckAccessAllowsUserWithAnyRequiredGroup(): void {
		$user = $this->createMock(IUser::class);
		$controller = $this->buildController($user, ['empleados', 'admin']);

		$controller->checkAccess(['admin']);
		$this->assertTrue(true);
	}

	private function buildController(?IUser $user, array $groupIds): BaseController {
		$request = $this->createMock(IRequest::class);
		$userSession = $this->createMock(IUserSession::class);
		$userSession->method('getUser')->willReturn($user);

		$groupManager = $this->createMock(IGroupManager::class);
		$groupManager->method('getUserGroupIds')->willReturn($groupIds);
		$groupManager->method('getUserGroups')->willReturn(array_map(function (string $groupId) {
			$group = $this->createMock(IGroup::class);
			$group->method('getGID')->willReturn($groupId);
			return $group;
		}, $groupIds));

		$empleadosMapper = $this->createMock(empleadosMapper::class);
		$configuracionesMapper = $this->createMock(configuracionesMapper::class);
		$configuracionesMapper->method('GetConfig')->willReturn([]);

		return new class('empleados', $request, $userSession, $groupManager, $empleadosMapper, $configuracionesMapper) extends BaseController {
		};
	}
}
