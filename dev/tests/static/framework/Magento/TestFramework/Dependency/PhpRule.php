<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestFramework\Dependency;

use Magento\Framework\App\Utility\Files;
use Magento\Framework\Config\Reader\Filesystem as ConfigReader;
use Magento\Framework\Exception\ConfigurationMismatchException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\UrlInterface;
use Magento\TestFramework\Dependency\Reader\ClassScanner;
use Magento\TestFramework\Dependency\Route\RouteMapper;
use Magento\TestFramework\Exception\NoSuchActionException;
use Magento\TestFramework\Inspection\Exception;

/**
 * Rule to check the dependencies between modules based on references, getUrl and layout blocks
 */
class PhpRule implements RuleInterface
{
    /**
     * List of filepaths for DI files
     *
     * @var array
     */
    private $diFiles;

    /**
     * Map from plugin classes to the subjects they modify
     *
     * @var array
     */
    private $pluginMap;

    /**
     * List of routers
     *
     * Format: array(
     *  '{Router}' => '{Module_Name}'
     * )
     *
     * @var array
     */
    private $_mapRouters = [];

    /**
     * List of layout blocks
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Block_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected $_mapLayoutBlocks = [];

    /**
     * Used to retrieve information from WebApi urls
     * @var ConfigReader
     */
    protected $configReader;

    /**
     * Default modules list.
     *
     * @var array
     */
    protected $_defaultModules = [
        'frontend' => 'Magento\Theme',
        'adminhtml' => 'Magento\Adminhtml',
    ];

    /**
     * @var RouteMapper
     */
    private $routeMapper;

    /**
     * Whitelists for dependency check
     *
     * @var array
     */
    private $whitelists;

    /**
     * @var ClassScanner
     */
    private $classScanner;

    /**
     * @var array
     */
    private $serviceMethods;

    /**
     * @param array $mapRouters
     * @param array $mapLayoutBlocks
     * @param ConfigReader $configReader
     * @param array $pluginMap
     * @param array $whitelists
     * @param ClassScanner|null $classScanner
     * @param RouteMapper|null $routeMapper
     */
    public function __construct(
        array $mapRouters,
        array $mapLayoutBlocks,
        ConfigReader $configReader,
        array $pluginMap = [],
        array $whitelists = [],
        ?ClassScanner $classScanner = null,
        ?RouteMapper $routeMapper = null
    ) {
        $this->_mapRouters = $mapRouters;
        $this->_mapLayoutBlocks = $mapLayoutBlocks;
        $this->configReader = $configReader;
        $this->pluginMap = $pluginMap ?: null;
        $this->whitelists = $whitelists;
        $this->classScanner = $classScanner ?? new ClassScanner();
        $this->routeMapper = $routeMapper ?? new RouteMapper();
    }

    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     * @throws \Exception
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (!in_array($fileType, ['php', 'template', 'fixture'])) {
            return [];
        }

        $dependenciesInfo = [];
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->caseClassesAndIdentifiers($currentModule, $file, $contents)
        );
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->_caseGetUrl($currentModule, $contents, $file)
        );
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->_caseLayoutBlock($currentModule, $fileType, $file, $contents)
        );
        return $dependenciesInfo;
    }

    /**
     * Get routes whitelist
     *
     * @return array
     */
    private function getRoutesWhitelist(): array
    {
        return $this->whitelists['routes'] ?? [];
    }

    /**
     * Check references to classes and identifiers defined in other modules
     *
     * @param string $currentModule
     * @param string $file
     * @param string $contents
     * @return array
     * @throws \Exception
     */
    private function caseClassesAndIdentifiers($currentModule, $file, &$contents)
    {
        $pattern = '~\b(?<class>(?<module>('
            .'(?:'
            . implode('|', Files::init()->getNamespaces())
            . ')'
            . '(?<delimiter>[_\\\\]))[A-Z]{1,}[a-zA-Z0-9]{1,})'
            . '(?<class_inside_module>\\4[a-zA-Z0-9_\\\\]{2,})?)\b'
            . '(?:::(?<module_scoped_key>[A-Za-z0-9_/.]+)[\'"])?~';

        if (!preg_match_all($pattern, $contents, $matches)) {
            return [];
        }

        $dependenciesInfo = [];
        $matches['module'] = array_unique($matches['module']);
        foreach ($matches['module'] as $i => $referenceModule) {
            $referenceModule = str_replace('_', '\\', $referenceModule);
            if ($currentModule == $referenceModule) {
                continue;
            }

            $dependencyClass = trim($matches['class'][$i]);
            if (empty($matches['class_inside_module'][$i]) && !empty($matches['module_scoped_key'][$i])) {
                $dependencyType = RuleInterface::TYPE_SOFT;
            } else {
                $currentClass = $this->getClassFromFilepath($file);
                $dependencyType = $this->isPluginDependency($currentClass, $dependencyClass)
                    ? RuleInterface::TYPE_SOFT
                    : RuleInterface::TYPE_HARD;
            }

            $dependenciesInfo[] = [
                'modules' => [$referenceModule],
                'type' => $dependencyType,
                'source' => $dependencyClass,
            ];
        }

        return $dependenciesInfo;
    }

    /**
     * Get class name from filename based on class/file naming conventions
     *
     * @param string $filepath
     * @return string
     */
    private function getClassFromFilepath(string $filepath): string
    {
        return $this->classScanner->getClassName($filepath);
    }

    /**
     * Load DI configuration files
     *
     * @return array
     * @throws \Exception
     */
    private function loadDiFiles()
    {
        if (!$this->diFiles) {
            $this->diFiles = Files::init()->getDiConfigs();
        }
        return $this->diFiles;
    }

    /**
     * Generate an array of plugin info
     *
     * @return array
     * @throws \Exception
     */
    private function loadPluginMap()
    {
        if (!$this->pluginMap) {
            foreach ($this->loadDiFiles() as $filepath) {
                $dom = new \DOMDocument();
                // phpcs:ignore Magento2.Functions.DiscouragedFunction
                $dom->loadXML(file_get_contents($filepath));
                $typeNodes = $dom->getElementsByTagName('type');
                /** @var \DOMElement $type */
                foreach ($typeNodes as $type) {
                    /** @var \DOMElement $plugin */
                    foreach ($type->getElementsByTagName('plugin') as $plugin) {
                        $subject = $type->getAttribute('name');
                        $pluginType = $plugin->getAttribute('type');
                        $this->pluginMap[$pluginType] = $subject;
                    }
                }
            }
        }
        return $this->pluginMap;
    }

    /**
     * Determine whether a the dependency relation is because of a plugin
     *
     * True IFF the dependent is a plugin for some class in the same module as the dependency.
     *
     * @param string $dependent
     * @param string $dependency
     * @return bool
     * @throws \Exception
     */
    private function isPluginDependency($dependent, $dependency)
    {
        $pluginMap = $this->loadPluginMap();
        $subject = isset($pluginMap[$dependent])
            ? $pluginMap[$dependent]
            : null;
        if ($subject === $dependency) {
            return true;
        } elseif ($subject) {
            $moduleNameLength = strpos($subject, '\\', strpos($subject, '\\') + 1);
            $subjectModule = substr($subject, 0, $moduleNameLength);
            return strpos($dependency, $subjectModule) === 0;
        } else {
            return false;
        }
    }

    /**
     * Check get URL method
     *
     * Ex.: getUrl('{path}')
     *
     * @param string $currentModule
     * @param string $contents
     * @param string $file
     * @return array
     * @throws LocalizedException
     */
    protected function _caseGetUrl(string $currentModule, string &$contents, string $file): array
    {
        $dependencies = [];
        $pattern = '#(\->|:)(?<source>getUrl\(([\'"])(?<path>[a-zA-Z0-9\-_*/]+)\3)\s*[,)]#';
        if (!preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            return $dependencies;
        }
        try {
            foreach ($matches as $item) {
                $path = $item['path'];
                $modules = [];
                if (strpos($path, '*') !== false) {
                    $modules = $this->processWildcardUrl($path, $file);
                } elseif (preg_match('#rest(?<service>/V1/.+)#i', $path, $apiMatch)) {
                    $modules = $this->processApiUrl($apiMatch['service']);
                } else {
                    $modules = $this->processStandardUrl($path);
                }
                if ($modules && !in_array($currentModule, $modules)) {
                    $dependencies[] = [
                        'modules' => $modules,
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => $item['source'],
                    ];
                }
            }
        } catch (NoSuchActionException $e) {
            if (array_search($e->getMessage(), $this->getRoutesWhitelist()) === false) {
                throw new LocalizedException(__('Invalid URL path: %1', $e->getMessage()), $e);
            }
        }
        return $dependencies;
    }

    /**
     * Helper method to get module dependencies used by a wildcard Url
     *
     * @param string $urlPath
     * @param string $filePath
     * @return string[]
     * @throws NoSuchActionException
     */
    private function processWildcardUrl(string $urlPath, string $filePath)
    {
        $filePath = strtolower($filePath);
        $urlRoutePieces = explode('/', $urlPath);
        $routeId = array_shift($urlRoutePieces);
        //Skip route wildcard processing as this requires using the routeMapper
        if ('*' === $routeId) {
            return [];
        }

        /**
         * Only handle Controllers. ie: Ignore Blocks, Templates, and Models due to complexity in static resolution
         * of route
         */
        if (!preg_match(
            '#controller/(adminhtml/)?(?<controller_name>.+)/(?<action_name>\w+).php$#',
            $filePath,
            $fileParts
        )) {
            return [];
        }

        $controllerName = array_shift($urlRoutePieces);
        if ('*' === $controllerName) {
            $controllerName = str_replace('/', '_', $fileParts['controller_name']);
        }

        if (empty($urlRoutePieces) || !$urlRoutePieces[0]) {
            $actionName = UrlInterface::DEFAULT_ACTION_NAME;
        } else {
            $actionName = array_shift($urlRoutePieces);
            if ('*' === $actionName) {
                $actionName = $fileParts['action_name'];
            }
        }

        return $this->routeMapper->getDependencyByRoutePath(
            strtolower($routeId),
            strtolower($controllerName),
            strtolower($actionName)
        );
    }

    /**
     * Helper method to get module dependencies used by a standard URL
     *
     * @param string $path
     * @return string[]
     * @throws NoSuchActionException
     */
    private function processStandardUrl(string $path)
    {
        $pattern = '#(?<route_id>[a-z0-9\-_]{3,})'
            . '(/(?<controller_name>[a-z0-9\-_]+))?(/(?<action_name>[a-z0-9\-_]+))?#i';
        if (!preg_match($pattern, $path, $match)) {
            throw new NoSuchActionException('Failed to parse standard url path: ' . $path);
        }
        $routeId = $match['route_id'];
        $controllerName = $match['controller_name'] ?? UrlInterface::DEFAULT_CONTROLLER_NAME;
        $actionName = $match['action_name'] ?? UrlInterface::DEFAULT_ACTION_NAME;

        return $this->routeMapper->getDependencyByRoutePath(
            $routeId,
            $controllerName,
            $actionName
        );
    }

    /**
     * Create regex patterns from service url paths
     *
     * @return array
     */
    private function getServiceMethodRegexps(): array
    {
        if (!$this->serviceMethods) {
            $this->serviceMethods = [];
            $serviceRoutes = $this->configReader->read()['routes'];
            foreach ($serviceRoutes as $serviceRouteUrl => $methods) {
                $pattern = '#:\w+#';
                $replace = '\w+';
                $serviceRouteUrlRegex = preg_replace($pattern, $replace, $serviceRouteUrl);
                $serviceRouteUrlRegex = '#^' . $serviceRouteUrlRegex . '$#';
                $this->serviceMethods[$serviceRouteUrlRegex] = $methods;
            }
        }
        return $this->serviceMethods;
    }

    /**
     * Helper method to get module dependencies used by an API URL
     *
     * @param string $path
     * @return string[]
     *
     * @throws NoSuchActionException
     * @throws Exception
     */
    private function processApiUrl(string $path): array
    {
        foreach ($this->getServiceMethodRegexps() as $serviceRouteUrlRegex => $methods) {
            /**
             * Since we expect that every service method should be within the same module, we can use the class from
             * any method
             */
            if (preg_match($serviceRouteUrlRegex, $path)) {
                $method = reset($methods);

                $className = $method['service']['class'];
                //get module from className
                if (preg_match('#^(?<module>\w+[\\\]\w+)#', $className, $match)) {
                    return [$match['module']];
                }
                throw new Exception('Failed to parse class from className: ' . $className);
            }
        }
        throw new NoSuchActionException('Failed to match service with url path: ' . $path);
    }

    /**
     * Check layout blocks
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    protected function _caseLayoutBlock(string $currentModule, string $fileType, string $file, string &$contents): array
    {
        $pattern = '/[\->:]+(?<source>(?:getBlock|getBlockHtml)\([\'"](?<block>[\w\.\-]+)[\'"]\))/';

        $area = $this->_getAreaByFile($file, $fileType);

        $result = [];
        if (!preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            return $result;
        }

        foreach ($matches as $match) {
            if (in_array($match['block'], ['root', 'content'])) {
                continue;
            }
            $check = $this->_checkDependencyLayoutBlock($currentModule, $area, $match['block']);
            $modules = isset($check['modules']) ? $check['modules'] : null;
            if ($modules) {
                foreach ($modules as $module) {
                    $result[$module] = [
                        'type' => RuleInterface::TYPE_HARD,
                        'source' => $match['source'],
                    ];
                }
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Get area from file path
     *
     * @param string $file
     * @param string $fileType
     * @return string|null
     */
    protected function _getAreaByFile(string $file, string $fileType): ?string
    {
        if ($fileType == 'php') {
            return null;
        }
        $area = 'default';
        if (preg_match('/\/(?<area>adminhtml|frontend)\//', $file, $matches)) {
            $area = $matches['area'];
        }
        return $area;
    }

    /**
     * Check layout block dependency
     *
     * Return: array(
     *  'modules'  // dependent modules
     *  'source'  // source text
     * )
     *
     * @param string $currentModule
     * @param string|null $area
     * @param string $block
     * @return array
     */
    protected function _checkDependencyLayoutBlock(string $currentModule, ?string $area, string $block): array
    {
        if (isset($this->_mapLayoutBlocks[$area][$block]) || $area === null) {
            // CASE 1: No dependencies
            $modules = [];
            if ($area === null) {
                foreach ($this->_mapLayoutBlocks as $blocks) {
                    if (array_key_exists($block, $blocks)) {
                        $modules += $blocks[$block];
                    }
                }
            } else {
                $modules = $this->_mapLayoutBlocks[$area][$block];
            }
            if (isset($modules[$currentModule])) {
                return ['modules' => []];
            }
            // CASE 2: Single dependency
            if (1 == count($modules)) {
                return ['modules' => $modules];
            }
            // CASE 3: Default module dependency
            $defaultModule = $this->_getDefaultModuleName($area);
            if (isset($modules[$defaultModule])) {
                return ['modules' => [$defaultModule]];
            }
        }
        // CASE 4: \Exception - Undefined block
        return [];
    }

    /**
     * Retrieve default module name (by area)
     *
     * @param string $area
     * @return null
     */
    protected function _getDefaultModuleName($area = 'default')
    {
        if (isset($this->_defaultModules[$area])) {
            return $this->_defaultModules[$area];
        }
        return null;
    }

    /**
     * Retrieve unique dependencies
     *
     * @param array $dependencies
     * @return array
     */
    protected function _getUniqueDependencies($dependencies = [])
    {
        $result = [];
        foreach ($dependencies as $module => $value) {
            $result[] = ['modules' => [$module], 'type' => $value['type'], 'source' => $value['source']];
        }
        return $result;
    }

    /**
     * Merge dependencies
     *
     * @param array $known
     * @param array $new
     * @return array
     */
    private function considerCaseDependencies($known, $new)
    {
        return array_merge($known, $new);
    }
}
