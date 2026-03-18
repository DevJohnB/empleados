<?php

declare(strict_types=1);

namespace Integrity;

use PHPUnit\Framework\TestCase;

class ControllerRouteIntegrityTest extends TestCase {
	public function testEachDeclaredRouteResolvesToAControllerClassAndMethod(): void {
		$routes = include __DIR__ . '/../../../appinfo/routes.php';

		foreach ($routes['routes'] as $route) {
			[$controllerName, $methodName] = explode('#', $route['name'], 2);
			$className = 'OCA\\Empleados\\Controller\\' . ucfirst($controllerName) . 'Controller';

			$this->assertTrue(class_exists($className), "Missing controller class for route {$route['name']}: {$className}");
			$this->assertTrue(method_exists($className, $methodName), "Missing method {$methodName} for route {$route['name']} on {$className}");
		}
	}

	public function testControllerFilesDoNotCollideByCasing(): void {
		$controllerFiles = glob(__DIR__ . '/../../../lib/Controller/*Controller.php');
		$namesByLowercase = [];

		foreach ($controllerFiles as $controllerFile) {
			$fileName = basename($controllerFile);
			$namesByLowercase[strtolower($fileName)][] = $fileName;
		}

		foreach ($namesByLowercase as $lowercaseName => $fileNames) {
			$this->assertCount(1, $fileNames, 'Duplicate controller file casing detected for ' . $lowercaseName);
		}
	}
}
