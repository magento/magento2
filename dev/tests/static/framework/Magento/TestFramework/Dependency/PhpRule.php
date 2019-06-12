<?php
/**
 * Rule for searching php file dependency
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Dependency;

use Magento\Framework\App\Utility\Files;

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
    protected $_mapRouters = [];

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
     * Default modules list.
     *
     * @var array
     */
    protected $_defaultModules = [
        'frontend' => 'Magento\Theme',
        'adminhtml' => 'Magento\Adminhtml',
    ];

    /**
     * Constructor
     *
     * @param array $mapRouters
     * @param array $mapLayoutBlocks
     * @param array $pluginMap
     */
    public function __construct(array $mapRouters, array $mapLayoutBlocks, array $pluginMap = [])
    {
        $this->_mapRouters = $mapRouters;
        $this->_mapLayoutBlocks = $mapLayoutBlocks;
        $this->_namespaces = implode('|', \Magento\Framework\App\Utility\Files::init()->getNamespaces());
        $this->pluginMap = $pluginMap ?: null;
    }

    /**
     * Gets alien dependencies information for current module by analyzing file's contents
     *
     * @param string $currentModule
     * @param string $fileType
     * @param string $file
     * @param string $contents
     * @return array
     */
    public function getDependencyInfo($currentModule, $fileType, $file, &$contents)
    {
        if (!in_array($fileType, ['php', 'template'])) {
            return [];
        }

        $dependenciesInfo = [];
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->caseClassesAndIdentifiers($currentModule, $file, $contents)
        );
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->_caseGetUrl($currentModule, $contents)
        );
        $dependenciesInfo = $this->considerCaseDependencies(
            $dependenciesInfo,
            $this->_caseLayoutBlock($currentModule, $fileType, $file, $contents)
        );
        return $dependenciesInfo;
    }

    /**
     * Check references to classes and identifiers defined in other modules
     *
     * @param string $currentModule
     * @param string $file
     * @param string $contents
     * @return array
     */
    private function caseClassesAndIdentifiers($currentModule, $file, &$contents)
    {
        $pattern = '~\b(?<class>(?<module>('
            . implode(
                '[_\\\\]|',
                Files::init()->getNamespaces()
            )
            . '[_\\\\])[a-zA-Z0-9]+)'
            . '(?<class_inside_module>[a-zA-Z0-9_\\\\]*))\b(?:::(?<module_scoped_key>[a-z0-9_]+)[\'"])?~';

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
                $currentClass = $this->getClassFromFilepath($file, $currentModule);
                $dependencyType = $this->isPluginDependency($currentClass, $dependencyClass)
                    ? RuleInterface::TYPE_SOFT
                    : RuleInterface::TYPE_HARD;
            }

            $dependenciesInfo[] = [
                'module' => $referenceModule,
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
     * @param string $module
     * @return string
     */
    private function getClassFromFilepath($filepath, $module)
    {
        $class = strstr($filepath, str_replace(['_', '\\', '/'], DIRECTORY_SEPARATOR, $module));
        $class = str_replace(DIRECTORY_SEPARATOR, '\\', strstr($class, '.php', true));
        return $class;
    }

    /**
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
     */
    private function loadPluginMap()
    {
        if (!$this->pluginMap) {
            foreach ($this->loadDiFiles() as $filepath) {
                $dom = new \DOMDocument();
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
            $subjectModule = substr($subject, 0, strpos($subject, '\\', 9)); // (strlen('Magento\\') + 1) === 9
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
     * @param $currentModule
     * @param $contents
     * @return array
     */
    protected function _caseGetUrl($currentModule, &$contents)
    {
        $pattern = '/[\->:]+(?<source>getUrl\([\'"](?<router>[\w\/*]+)[\'"])/';

        $dependencies = [];
        if (!preg_match_all($pattern, $contents, $matches, PREG_SET_ORDER)) {
            return $dependencies;
        }

        foreach ($matches as $item) {
            $router = str_replace('/', '\\', $item['router']);
            if (isset($this->_mapRouters[$router])) {
                $modules = $this->_mapRouters[$router];
                if (!in_array($currentModule, $modules)) {
                    foreach ($modules as $module) {
                        $dependencies[] = [
                            'module' => $module,
                            'type' => RuleInterface::TYPE_HARD,
                            'source' => $item['source'],
                        ];
                    }
                }
            }
        }
        return $dependencies;
    }

    /**
     * Check layout blocks
     *
     * @param $currentModule
     * @param $fileType
     * @param $file
     * @param $contents
     * @return array
     */
    protected function _caseLayoutBlock($currentModule, $fileType, $file, &$contents)
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
            $module = isset($check['module']) ? $check['module'] : null;
            if ($module) {
                $result[$module] = [
                    'type' => RuleInterface::TYPE_HARD,
                    'source' => $match['source'],
                ];
            }
        }
        return $this->_getUniqueDependencies($result);
    }

    /**
     * Get area from file path
     *
     * @param $file
     * @param $fileType
     * @return string|null
     */
    protected function _getAreaByFile($file, $fileType)
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
     *  'module'  // dependent module
     *  'source'  // source text
     * )
     *
     * @param $currentModule
     * @param $area
     * @param $block
     * @return array
     */
    protected function _checkDependencyLayoutBlock($currentModule, $area, $block)
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
                return ['module' => null];
            }
            // CASE 2: Single dependency
            if (1 == count($modules)) {
                return ['module' => current($modules)];
            }
            // CASE 3: Default module dependency
            $defaultModule = $this->_getDefaultModuleName($area);
            if (isset($modules[$defaultModule])) {
                return ['module' => $defaultModule];
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
            $result[] = ['module' => $module, 'type' => $value['type'], 'source' => $value['source']];
        }
        return $result;
    }

    /**
     * @param array $known
     * @param array $new
     * @return array
     */
    private function considerCaseDependencies($known, $new)
    {
        return array_merge($known, $new);
    }
}
