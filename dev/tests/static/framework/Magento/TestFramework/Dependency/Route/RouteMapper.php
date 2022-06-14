<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Dependency\Route;

use Exception;
use Magento\Framework\App\Area;
use Magento\Framework\App\Utility\Files;
use Magento\Framework\Component\ComponentFile;
use Magento\TestFramework\Exception\NoSuchActionException;

/**
 * Route mapper based on routes.xml declarations
 */
class RouteMapper
{
    /**
     * List of routers
     *
     * Format: array(
     *  '{Router_Id}' => '{Route_Id}' => ['{Module_Name}']
     * )
     *
     * @var array
     */
    private $routers = [];

    /**
     * List of routes.xml files by modules
     *
     * Format: array(
     *  '{Module_Name}' => ['{Filename}']
     * )
     *
     * @var array
     */
    private $routeConfigFiles = [];

    /**
     * List of controllers actions
     *
     * Format: array(
     *  '{Router_Id}' => '{Route_Id}' => '{Controller_Name}' => '{Action_Name}' => [{'Module_Name'}]
     * )
     *
     * @var array
     */
    private $actions = [];

    /**
     * List of reserved words
     *
     * @see \Magento\Framework\App\Router\ActionList
     * @var array
     */
    private $reservedWords = [
        'abstract' => 1,
        'and' => 1,
        'array' => 1,
        'as' => 1,
        'break' => 1,
        'callable' => 1,
        'case' => 1,
        'catch' => 1,
        'class' => 1,
        'clone' => 1,
        'const' => 1,
        'continue' => 1,
        'declare' => 1,
        'default' => 1,
        'die' => 1,
        'do' => 1,
        'echo' => 1,
        'else' => 1,
        'elseif' => 1,
        'empty' => 1,
        'enddeclare' => 1,
        'endfor' => 1,
        'endforeach' => 1,
        'endif' => 1,
        'endswitch' => 1,
        'endwhile' => 1,
        'eval' => 1,
        'exit' => 1,
        'extends' => 1,
        'final' => 1,
        'finally' => 1,
        'fn' => 1,
        'for' => 1,
        'foreach' => 1,
        'function' => 1,
        'global' => 1,
        'goto' => 1,
        'if' => 1,
        'implements' => 1,
        'include' => 1,
        'instanceof' => 1,
        'insteadof' => 1,
        'interface' => 1,
        'isset' => 1,
        'list' => 1,
        'match' => 1,
        'namespace' => 1,
        'new' => 1,
        'or' => 1,
        'print' => 1,
        'private' => 1,
        'protected' => 1,
        'public' => 1,
        'require' => 1,
        'return' => 1,
        'static' => 1,
        'switch' => 1,
        'throw' => 1,
        'trait' => 1,
        'try' => 1,
        'unset' => 1,
        'use' => 1,
        'var' => 1,
        'void' => 1,
        'while' => 1,
        'xor' => 1,
        'yield' => 1
    ];

    /**
     * Provide routing declaration by router_id
     *
     * @param string $routerId
     * @return array
     * @throws Exception
     */
    public function getRoutes(string $routerId = ''): array
    {
        $routes = [];
        if (!$routerId) {
            foreach ($this->getRoutersMap() as $routesByRouterType) {
                $routes = array_merge_recursive($routes, $routesByRouterType);
            }
            array_walk(
                $routes,
                function (&$modules) {
                    $modules = array_unique($modules);
                }
            );
        } else {
            $routes = $this->getRoutersMap()[$routerId] ?? [];
        }
        return $routes;
    }

    /**
     * Provide dependencies for a specific URL path
     *
     * @param string $routeId
     * @param string $controllerName
     * @param string $actionName
     * @return string[]
     * @throws NoSuchActionException
     * @throws Exception
     */
    public function getDependencyByRoutePath(
        string $routeId,
        string $controllerName,
        string $actionName
    ): array {
        $routeId = strtolower($routeId);
        $controllerName = strtolower($controllerName);
        $actionName = strtolower($actionName);

        if (isset($this->reservedWords[$actionName])) {
            $actionName .= 'action';
        }

        $dependencies = [];
        foreach ($this->getRouterTypes() as $routerId) {
            if (isset($this->getActionsMap()[$routerId][$routeId][$controllerName][$actionName])) {
                $dependencies[] = $this->getActionsMap()[$routerId][$routeId][$controllerName][$actionName];
            }
        }
        $dependencies = array_merge([], ...$dependencies);

        if (empty($dependencies)) {
            throw new NoSuchActionException(implode('/', [$routeId, $controllerName, $actionName]));
        } else {
            $dependencies = array_unique($dependencies);
        }
        return $dependencies;
    }

    /**
     * Provide a list of router type
     *
     * @return array
     * @throws Exception
     */
    private function getRouterTypes()
    {
        return array_keys($this->getActionsMap());
    }

    /**
     * Provide routing declaration
     *
     * @return array
     * @throws Exception
     */
    private function getRoutersMap()
    {
        if (empty($this->routers)) {
            foreach ($this->getListRoutesXml() as $module => $configFiles) {
                foreach ($configFiles as $configFile) {
                    $this->processConfigFile($module, $configFile);
                }
            }
        }

        return $this->routers;
    }

    /**
     * Update routers map for the module basing on the routing config file
     *
     * @param string $module
     * @param string $configFile
     *
     * @return void
     */
    private function processConfigFile(string $module, string $configFile)
    {
        // Read module's routes.xml file
        $config = simplexml_load_file($configFile);

        $routers = $config->xpath("/config/router");
        foreach ($routers as $router) {
            $routerId = (string)$router['id'];
            foreach ($router->xpath('route') as $route) {
                $routeId = (string)$route['id'];
                if (!isset($this->routers[$routerId][$routeId])) {
                    $this->routers[$routerId][$routeId] = [];
                }
                if (!in_array($module, $this->routers[$routerId][$routeId])) {
                    $this->routers[$routerId][$routeId][] = $module;
                }
            }
        }
    }

    /**
     * Prepare the list of routes.xml files (by modules)
     *
     * @throws Exception
     */
    private function getListRoutesXml()
    {
        if (empty($this->routeConfigFiles)) {
            $files = Files::init()->getConfigFiles('*/routes.xml', [], false, true);
            /** @var ComponentFile $componentFile */
            foreach ($files as $componentFile) {
                $module = str_replace('_', '\\', $componentFile->getComponentName());
                $this->routeConfigFiles[$module][] = $componentFile->getFullPath();
            }
        }
        return $this->routeConfigFiles;
    }

    /**
     * Provide a list of available actions
     *
     * @return array
     * @throws Exception
     */
    private function getActionsMap(): array
    {
        if (empty($this->actions)) {
            $files = Files::init()->getPhpFiles(Files::INCLUDE_APP_CODE);
            $actionsMap = [];
            foreach ($this->getRoutersMap() as $routerId => $routes) {
                $actionsMapPerArea = [];
                foreach ($routes as $routeId => $dependencies) {
                    $actionsMapPerArea[$routeId] = [];
                    foreach ($dependencies as $module) {
                        $moduleActions = $this->getModuleActionsMapping($module, $routerId, $files);
                        $actionsMapPerArea[$routeId] =
                            array_merge_recursive($actionsMapPerArea[$routeId], $moduleActions);
                    }
                }
                $actionsMap[$routerId] = $actionsMapPerArea;
            }
            $this->actions = $actionsMap;
        }

        return $this->actions;
    }

    /**
     * Provide a list of available module actions by router_id
     *
     * @param string $module
     * @param string $routerId
     * @param array $files
     * @return array
     */
    private function getModuleActionsMapping(string $module, string $routerId, array $files): array
    {
        $subdirectoryPattern = str_replace('\\', DIRECTORY_SEPARATOR, $module);
        $subdirectoryPattern .= DIRECTORY_SEPARATOR . 'Controller/';
        if (array_search($routerId, [Area::AREA_ADMINHTML, Area::AREA_ADMIN], true) !== false) {
            $subdirectoryPattern .= ucfirst(Area::AREA_ADMINHTML) . DIRECTORY_SEPARATOR;
        } else {
            $subdirectoryPattern .= '(?!' . ucfirst(Area::AREA_ADMINHTML) . ')';
        }

        $actions = preg_grep('~' . $subdirectoryPattern . '~', $files);

        $actionsPattern = '#' . $subdirectoryPattern . '(?<controller>\S+)/'
            . '(?<action_name>\w+)\.php$#';

        $moduleActionsMapping = [];
        foreach ($actions as $action) {
            if (preg_match($actionsPattern, $action, $matches)) {
                $controllerName = strtolower(str_replace('/', '_', $matches['controller']));
                $actionName = strtolower($matches['action_name']);
                if (!isset($moduleActionsMapping[$controllerName][$actionName])) {
                    $moduleActionsMapping[$controllerName][$actionName] = [];
                }
                $moduleActionsMapping[$controllerName][$actionName][] = $module;
            }
        }

        return $moduleActionsMapping;
    }
}
