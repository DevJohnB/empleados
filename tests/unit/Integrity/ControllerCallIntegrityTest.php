<?php

declare(strict_types=1);

namespace Integrity;

use PHPUnit\Framework\TestCase;

class ControllerCallIntegrityTest extends TestCase {
	private const GENERIC_INHERITED_METHODS = [
		'insert',
		'update',
		'find',
		'findAll',
		'findEntity',
		'findEntities',
	];

	public function testInjectedCollaboratorMethodCallsResolveToExistingMethods(): void {
		$classMethods = $this->indexClassMethods();

		foreach (glob(__DIR__ . '/../../../lib/Controller/*Controller.php') as $controllerFile) {
			$source = file_get_contents($controllerFile);
			$constructorParameterTypes = $this->extractConstructorParameterTypes($source);
			$propertyTypes = $this->extractAssignedPropertyTypes($source, $constructorParameterTypes);

			preg_match_all('/\\$this->([A-Za-z_][A-Za-z0-9_]*)->([A-Za-z_][A-Za-z0-9_]*)\\s*\\(/', $source, $matches, PREG_SET_ORDER);

			foreach ($matches as [, $propertyName, $methodName]) {
				if (in_array($methodName, self::GENERIC_INHERITED_METHODS, true)) {
					continue;
				}

				$collaboratorClass = $propertyTypes[$propertyName] ?? null;
				if ($collaboratorClass === null || !isset($classMethods[$collaboratorClass])) {
					continue;
				}

				$this->assertContains(
					$methodName,
					$classMethods[$collaboratorClass],
					sprintf(
						'Method %s::%s() referenced from %s via $this->%s does not exist',
						$collaboratorClass,
						$methodName,
						basename($controllerFile),
						$propertyName
					)
				);
			}
		}
	}

	/**
	 * @return array<string, list<string>>
	 */
	private function indexClassMethods(): array {
		$index = [];

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator(__DIR__ . '/../../../lib', \FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $fileInfo) {
			if ($fileInfo->getExtension() !== 'php') {
				continue;
			}

			$source = file_get_contents($fileInfo->getPathname());
			if (!preg_match('/class\\s+([A-Za-z_][A-Za-z0-9_]*)/', $source, $classMatch)) {
				continue;
			}

			preg_match_all('/function\\s+([A-Za-z_][A-Za-z0-9_]*)\\s*\\(/', $source, $methodMatches);
			$index[$classMatch[1]] = $methodMatches[1];
		}

		return $index;
	}

	/**
	 * @return array<string, string>
	 */
	private function extractConstructorParameterTypes(string $source): array {
		$types = [];

		if (!preg_match('/function\\s+__construct\\s*\\((.*?)\\)\\s*\\{/s', $source, $constructorMatch)) {
			return $types;
		}

		preg_match_all('/([A-Za-z_\\\\][A-Za-z0-9_\\\\]*)\\s+\\$([A-Za-z_][A-Za-z0-9_]*)/', $constructorMatch[1], $parameterMatches, PREG_SET_ORDER);
		foreach ($parameterMatches as [, $typeName, $variableName]) {
			$parts = explode('\\', $typeName);
			$types[$variableName] = end($parts);
		}

		return $types;
	}

	/**
	 * @param array<string, string> $constructorParameterTypes
	 * @return array<string, string>
	 */
	private function extractAssignedPropertyTypes(string $source, array $constructorParameterTypes): array {
		$propertyTypes = [];

		preg_match_all('/\\$this->([A-Za-z_][A-Za-z0-9_]*)\\s*=\\s*\\$([A-Za-z_][A-Za-z0-9_]*)\\s*;/', $source, $assignmentMatches, PREG_SET_ORDER);
		foreach ($assignmentMatches as [, $propertyName, $variableName]) {
			if (isset($constructorParameterTypes[$variableName])) {
				$propertyTypes[$propertyName] = $constructorParameterTypes[$variableName];
			}
		}

		return $propertyTypes;
	}
}
