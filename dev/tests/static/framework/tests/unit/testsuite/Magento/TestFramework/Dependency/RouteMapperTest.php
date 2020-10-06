<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\TestFramework\Dependency\Route\RouteMapper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test cases for class @see \Magento\TestFramework\Dependency\Route\RouteMapper
 */
class RouteMapperTest extends TestCase
{
    /**
     * @var RouteMapper
     */
    private $routerMap;

    public function setUp(): void
    {
        $routes = [
            new \Magento\Framework\Component\ComponentFile(
                ComponentRegistrar::MODULE,
                'Module_A',
                $this->getRouterPath('A', false)
            ),
            new \Magento\Framework\Component\ComponentFile(
                ComponentRegistrar::MODULE,
                'Module_A',
                $this->getRouterPath('A', true)
            ),
            new \Magento\Framework\Component\ComponentFile(
                ComponentRegistrar::MODULE,
                'Module_B',
                $this->getRouterPath('B', false)
            ),
            new \Magento\Framework\Component\ComponentFile(
                ComponentRegistrar::MODULE,
                'Module_B',
                $this->getRouterPath('B', true)
            )
        ];
        /** @var MockObject $fileUtilities */
        $fileUtilities = $this->createMock(Files::class);
        $fileUtilities->method('getConfigFiles')
            ->with('*/routes.xml', [], false, true)
            ->willReturn($routes);
        $fileUtilities->method('getModuleFile')->willReturnMap(
            [
                [
                    'Module',
                    'A',
                    'Controller/',
                    $this->getModuleDir('A') . DIRECTORY_SEPARATOR . 'Controller/'
                ],
                [
                    'Module',
                    'B',
                    'Controller/',
                    $this->getModuleDir('B') . DIRECTORY_SEPARATOR . 'Controller/'
                ],
            ]
        );
        $fileUtilities->method('getPhpFiles')->with(Files::INCLUDE_APP_CODE)
            ->willReturn([
                $this->getControllerPath('A', 'ControllerA1', 'ActionA1', true),
                $this->getControllerPath('A', 'ControllerA1', 'ActionA1', false),
                $this->getControllerPath('A', 'ControllerA2', 'ActionA1', false),
                $this->getControllerPath('B', 'ControllerB1', 'ActionB1', true),
                $this->getControllerPath('B', 'ControllerB1', 'ActionB1', false),
                $this->getControllerPath('B', 'ControllerB1', 'ActionB2', false),
            ]);

        $this->routerMap = new RouteMapper($fileUtilities);
    }

    /**
     * @param string $routeId
     * @param string $controller
     * @param string $action
     * @param array $dependency
     *
     * @dataProvider getRoutesDataProvider
     */
    public function testGetDependencyByRoutePath(
        string $routeId,
        string $controller,
        string $action,
        array $dependency
    ): void {
        $this->assertSame($dependency, $this->routerMap->getDependencyByRoutePath($routeId, $controller, $action));
    }

    /**
     * Routes Data Provider.
     *
     * @return \string[][]
     */
    public function getRoutesDataProvider(): array
    {
        return [
            ['adminhtml', 'controllera1', 'actiona1', ['Module\A']],
            ['modulea', 'controllerA1', 'actionA1', ['Module\A']],
            ['modulea', 'controllera2', 'actiona1', ['Module\A']],
            ['adminhtml', 'controllerB1', 'actionB1', ['Module\B']],
            ['moduleb', 'controllerb1', 'actionb1', ['Module\B']],
            ['moduleb', 'controllerb1', 'actionb2', ['Module\B']],
            ['modulebfrontname', 'controllerb1', 'actionb1', ['Module\B']],
            ['modulebfrontname', 'controllerb1', 'actionb2', ['Module\B']],
        ];
    }

    public function testWrongRouterPath(): void
    {
        $this->expectException(\Magento\TestFramework\Exception\NoSuchActionException::class);
        $this->expectExceptionMessage('unknown/controller/action');

        $this->routerMap->getDependencyByRoutePath('unknown', 'controller', 'action');
    }

    /**
     * Get fake path to controller file.
     *
     * @param string $moduleName
     * @param string $controllerName
     * @param string $actionName
     * @param bool $isAdmin
     *
     * @return string
     */
    private function getControllerPath(
        string $moduleName,
        string $controllerName,
        string $actionName,
        bool $isAdmin
    ): string {
        return $this->getModuleDir($moduleName) . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR .
            ($isAdmin ? 'Adminhtml' . DIRECTORY_SEPARATOR : '') .
            $controllerName . DIRECTORY_SEPARATOR . $actionName . '.php';
    }

    /**
     * Get Module dir.
     *
     * @param string $moduleName
     *
     * @return string
     */
    private function getModuleDir(string $moduleName): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . '_files' . DIRECTORY_SEPARATOR . 'route_mapper' .
            DIRECTORY_SEPARATOR . 'Module' . DIRECTORY_SEPARATOR . $moduleName;
    }

    /**
     * Get path to routes.xml
     *
     * @param string $module
     * @param false $isAdmin
     *
     * @return string
     */
    private function getRouterPath(string $module, $isAdmin = false): string
    {
        return $this->getModuleDir($module) . DIRECTORY_SEPARATOR .
            'etc' . DIRECTORY_SEPARATOR . ($isAdmin ? 'adminhtml' : 'frontend') . DIRECTORY_SEPARATOR . 'routes.xml';
    }
}
