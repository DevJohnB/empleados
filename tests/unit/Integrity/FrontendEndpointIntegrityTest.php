<?php

declare(strict_types=1);

namespace Integrity;

use PHPUnit\Framework\TestCase;

class FrontendEndpointIntegrityTest extends TestCase {
	public function testGenerateUrlCallsPointToDeclaredBackendRoutes(): void {
		$routeUrls = $this->loadRouteUrls();

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(__DIR__ . '/../../../src', \FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $fileInfo) {
			if ($fileInfo->getExtension() !== 'vue' && $fileInfo->getExtension() !== 'js') {
				continue;
			}

			$source = file_get_contents($fileInfo->getPathname());
			$source = preg_replace('/\\/\\/.*$/m', '', $source);
			$source = preg_replace('/\\/\\*.*?\\*\\//s', '', $source);

			preg_match_all('/generateUrl\\((["\'])(\\/apps\\/empleados\\/[^"\']+)\\1\\)/', $source, $matches, PREG_SET_ORDER);
			foreach ($matches as $match) {
				$routePath = str_replace('/apps/empleados', '', $match[2]);
				if (str_contains($routePath, '{')) {
					continue;
				}

				$this->assertArrayHasKey(
					$routePath,
					$routeUrls,
					sprintf('Frontend endpoint %s referenced in %s is not declared in appinfo/routes.php', $match[2], $fileInfo->getPathname())
				);
			}
		}
	}

	/**
	 * @return array<string, string>
	 */
	private function loadRouteUrls(): array {
		$routes = include __DIR__ . '/../../../appinfo/routes.php';
		$routeUrls = [];

		foreach ($routes['routes'] as $route) {
			$routeUrls[$route['url']] = $route['name'];
		}

		return $routeUrls;
	}
}
