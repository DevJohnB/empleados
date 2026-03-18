<?php

declare(strict_types=1);

namespace Controller;

use OCA\Empleados\AppInfo\Application;
use OCA\Empleados\Controller\ApiController;
use OCP\IRequest;
use PHPUnit\Framework\TestCase;

class ApiTest extends TestCase {
	public function testIndexReturnsHelloWorldPayload(): void {
		$request = $this->createMock(IRequest::class);
		$controller = new ApiController(Application::APP_ID, $request);

		$this->assertEquals($controller->index()->getData()['message'], 'Hello world!');
	}

	public function testIndexReturnsDataResponse(): void {
		$request = $this->createMock(IRequest::class);
		$controller = new ApiController(Application::APP_ID, $request);

		$this->assertInstanceOf(\OCP\AppFramework\Http\DataResponse::class, $controller->index());
	}
}
