<?php
/**
 * Scan source code for incorrect or undeclared modules dependencies
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 */
namespace Magento\Test\Integrity;

/**
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Types of dependencies between modules
     */
    const TYPE_SOFT = 'soft';

    const TYPE_HARD = 'hard';

    /**
     * Types of dependencies map arrays
     */
    const MAP_TYPE_DECLARED = 'declared';

    const MAP_TYPE_FOUND = 'found';

    const MAP_TYPE_REDUNDANT = 'redundant';

    /**
     * Count of directories in path
     */
    const DIR_PATH_COUNT = 4;

    /**
     * List of config.xml files by modules
     *
     * Format: array(
     *  '{Module_Name}' => '{Filename}'
     * )
     *
     * @var array
     */
    protected static $_listConfigXml = array();

    /**
     * List of routes.xml files by modules
     *
     * Format: array(
     *  '{Module_Name}' => '{Filename}'
     * )
     *
     * @var array
     */
    protected static $_listRoutesXml = array();

    /**
     * List of routers
     *
     * Format: array(
     *  '{Router}' => '{Module_Name}'
     * )
     *
     * @var array
     */
    protected static $_mapRouters = array();

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
    protected static $_mapLayoutBlocks = array();

    /**
     * List of layout handles
     *
     * Format: array(
     *  '{Area}' => array(
     *   '{Handle_Name}' => array('{Module_Name}' => '{Module_Name}')
     * ))
     *
     * @var array
     */
    protected static $_mapLayoutHandles = array();

    /**
     * List of dependencies
     *
     * Format: array(
     *  '{Module_Name}' => array(
     *   '{Type}' => array(
     *    'declared'  = array('{Dependency}', ...)
     *    'found'     = array('{Dependency}', ...)
     *    'redundant' = array('{Dependency}', ...)
     * )))
     * @var array
     */
    protected static $_mapDependencies = array();

    /**
     * Regex pattern for validation file path of theme
     *
     * @var string
     */
    protected static $_defaultThemes = '';

    /**
     * Namespaces to analyze
     *
     * Format: {Namespace}|{Namespace}|...
     *
     * @var string
     */
    protected static $_namespaces;

    /**
     * Rule instances
     *
     * @var array
     */
    protected static $_rulesInstances = array();

    /**
     * Sets up data
     */
    public static function setUpBeforeClass()
    {
        self::$_namespaces = implode('|', \Magento\TestFramework\Utility\Files::init()->getNamespaces());

        self::_prepareListConfigXml();
        self::_prepareListRoutesXml();

        self::_prepareMapRouters();
        self::_prepareMapLayoutBlocks();
        self::_prepareMapLayoutHandles();

        self::_initDependencies();
        self::_initThemes();
        self::_initRules();
    }

    /**
     * Initialize default themes
     */
    protected static function _initThemes()
    {
        $defaultThemes = array();
        foreach (self::$_listConfigXml as $file) {
            $config = simplexml_load_file($file);
            $nodes = @($config->xpath("/config/*/design/theme/full_name") ?: array());
            foreach ($nodes as $node) {
                $defaultThemes[] = (string)$node;
            }
        }
        self::$_defaultThemes = sprintf('#app/design.*/(%s)/.*#', implode('|', array_unique($defaultThemes)));
    }

    /**
     * Create rules objects
     */
    protected static function _initRules()
    {
        $replaceFilePattern = str_replace('\\', '/', realpath(__DIR__)) . '/_files/dependency_test/*.php';
        $dbRuleTables = array();
        foreach (glob($replaceFilePattern) as $fileName) {
            $dbRuleTables = array_merge($dbRuleTables, @include $fileName);
        }
        self::$_rulesInstances = array(
            new \Magento\TestFramework\Dependency\PhpRule(self::$_mapRouters, self::$_mapLayoutBlocks),
            new \Magento\TestFramework\Dependency\DbRule($dbRuleTables),
            new \Magento\TestFramework\Dependency\LayoutRule(
                self::$_mapRouters,
                self::$_mapLayoutBlocks,
                self::$_mapLayoutHandles
            )
        );
    }

    /**
     * Return cleaned file contents
     *
     * @param string $fileType
     * @param string $file
     * @return string
     */
    protected function _getCleanedFileContents($fileType, $file)
    {
        $contents = (string)file_get_contents($file);
        switch ($fileType) {
            case 'php':
                //Removing php comments
                $contents = preg_replace('~/\*.*?\*/~s', '', $contents);
                $contents = preg_replace('~^\s*//.*$~m', '', $contents);
                break;
            case 'layout':
            case 'config':
                //Removing xml comments
                $contents = preg_replace('~\<!\-\-/.*?\-\-\>~s', '', $contents);
                break;
            case 'template':
                //Removing html
                $contentsWithoutHtml = '';
                preg_replace_callback(
                    '~(<\?php\s+.*\?>)~sU',
                    function ($matches) use ($contents, &$contentsWithoutHtml) {
                        $contentsWithoutHtml .= $matches[1];
                        return $contents;
                    },
                    $contents
                );
                $contents = $contentsWithoutHtml;
                //Removing php comments
                $contents = preg_replace('~/\*.*?\*/~s', '', $contents);
                $contents = preg_replace('~^\s*//.*$~s', '', $contents);
                break;
        }
        return $contents;
    }

    public function testUndeclared()
    {
        $invoker = new \Magento\TestFramework\Utility\AggregateInvoker($this);
        $invoker(
            /**
             * Check undeclared modules dependencies for specified file
             *
             * @param string $fileType
             * @param string $file
             */
            function ($fileType, $file) {
                // Validates file when it is belonged to default themes
                $filename = self::_getRelativeFilename($file);
                $isThemeFile = (bool)preg_match(self::$_defaultThemes, $filename);

                if (strpos($file, 'app/code') === false && !$isThemeFile) {
                    return;
                }

                $module = $this->_getModuleName($file);
                $contents = $this->_getCleanedFileContents($fileType, $file);

                // Apply rules
                $dependencies = array();
                foreach (self::$_rulesInstances as $rule) {
                    /** @var \Magento\TestFramework\Dependency\RuleInterface $rule */
                    $newDependencies = $rule->getDependencyInfo($module, $fileType, $file, $contents);
                    $dependencies = array_merge($dependencies, $newDependencies);
                }

                // Collect dependencies
                $undeclaredDependency = $this->_collectDependencies($module, $dependencies);

                // Prepare output message
                $result = array();
                foreach ($undeclaredDependency as $type => $modules) {
                    $modules = array_unique($modules);
                    if (!count($modules)) {
                        continue;
                    }
                    $result[] = sprintf("%s [%s]", $type, implode(', ', $modules));
                }
                if (count($result)) {
                    $this->fail('Module ' . $module . ' has undeclared dependencies: ' . implode(', ', $result));
                }
            },
            $this->getAllFiles()
        );
    }

    /**
     * Collect dependencies
     *
     * @param string $currentModuleName
     * @param array $dependencies
     * @return array
     */
    protected function _collectDependencies($currentModuleName, $dependencies = array())
    {
        if (!count($dependencies)) {
            return array();
        }
        $undeclared = array();
        foreach ($dependencies as $dependency) {
            $this->collectDependency($dependency, $currentModuleName, $undeclared);
        }
        return $undeclared;
    }

    /**
     * Collect a dependency
     *
     * @param string $currentModule
     * @param array $dependency
     * @param array $undeclared
     */
    private function collectDependency($dependency, $currentModule, &$undeclared)
    {
        $module = $dependency['module'];
        $nsModule = str_replace('_', '\\', $module);
        $type = isset($dependency['type']) ? $dependency['type'] : self::TYPE_HARD;

        $soft = $this->_getDependencies($currentModule, self::TYPE_SOFT, self::MAP_TYPE_DECLARED);
        $hard = $this->_getDependencies($currentModule, self::TYPE_HARD, self::MAP_TYPE_DECLARED);

        $declared = $type == self::TYPE_SOFT ? array_merge($soft, $hard) : $hard;
        if (!in_array($module, $declared) && !in_array($nsModule, $declared) && !$this->_isFake($module)) {
            $undeclared[$type][] = $module;
        } elseif ((in_array($module, $declared) || in_array($nsModule, $declared)) && $this->_isFake($module)) {
            $this->_setDependencies($currentModule, $type, self::MAP_TYPE_REDUNDANT, $module);
        }

        $this->_addDependencies($currentModule, $type, self::MAP_TYPE_FOUND, $nsModule);
    }

    /**
     * Collect redundant dependencies
     *
     * @test
     * @depends testUndeclared
     */
    public function collectRedundant()
    {
        foreach (array_keys(self::$_mapDependencies) as $module) {
            // Override 'soft' dependencies with 'hard'
            $soft = $this->_getDependencies($module, self::TYPE_SOFT, self::MAP_TYPE_FOUND);
            $hard = $this->_getDependencies($module, self::TYPE_HARD, self::MAP_TYPE_FOUND);
            $this->_setDependencies($module, self::TYPE_SOFT, self::MAP_TYPE_FOUND, array_diff($soft, $hard));
            foreach ($this->_getTypes() as $type) {
                $declared = $this->_getDependencies($module, $type, self::MAP_TYPE_DECLARED);
                $found = $this->_getDependencies($module, $type, self::MAP_TYPE_FOUND);
                $this->_setDependencies($module, $type, self::MAP_TYPE_REDUNDANT, array_diff($declared, $found));
            }
        }
    }

    /**
     * Check redundant dependencies
     *
     * @depends collectRedundant
     */
    public function testRedundant()
    {
        $output = array();
        foreach (array_keys(self::$_mapDependencies) as $module) {
            $result = array();
            foreach ($this->_getTypes() as $type) {
                $redundant = $this->_getDependencies($module, $type, self::MAP_TYPE_REDUNDANT);
                if (count($redundant)) {
                    $result[] = sprintf(
                        "\r\nModule %s: %s [%s]",
                        $module,
                        $type,
                        implode(', ', array_values($redundant))
                    );
                }
            }
            if (count($result)) {
                $output[] = implode(', ', $result);
            }
        }
        if (count($output)) {
            $this->fail("Redundant dependencies found!\r\n" . implode(' ', $output));
        }
    }

    /**
     * Extract Magento relative filename from absolute filename
     *
     * @param string $absoluteFilename
     * @return string
     */
    protected static function _getRelativeFilename($absoluteFilename)
    {
        $pathToSource = \Magento\TestFramework\Utility\Files::init()->getPathToSource();
        $relativeFileName = str_replace($pathToSource, '', $absoluteFilename);
        return trim(str_replace('\\', '/', $relativeFileName), '/');
    }

    /**
     * Extract module name from file path
     *
     * @param $absoluteFilename
     * @return string
     */
    protected function _getModuleName($absoluteFilename)
    {
        $file = self::_getRelativeFilename($absoluteFilename);
        if (preg_match('/\/(?<namespace>' . self::$_namespaces . ')[\/_\\\\]?(?<module>[^\/]+)\//', $file, $matches)) {
            return $matches['namespace'] . '\\' . $matches['module'];
        }
        return '';
    }

    /**
     * Convert file list to data provider structure
     *
     * @param string $fileType
     * @param array $files
     * @param bool|null $skip
     * @return array
     */
    protected function _prepareFiles($fileType, $files, $skip = null)
    {
        $result = array();
        foreach ($files as $relativePath => $file) {
            $absolutePath = $file[0];
            if (!$skip && substr_count($relativePath, '/') < self::DIR_PATH_COUNT) {
                continue;
            }
            $result[$relativePath] = array($fileType, $absolutePath);
        }
        return $result;
    }

    /**
     * Return all files
     *
     * @return array
     */
    public function getAllFiles()
    {
        $files = array();

        // Get all php files
        $files = array_merge(
            $files,
            $this->_prepareFiles(
                'php',
                \Magento\TestFramework\Utility\Files::init()->getPhpFiles(true, false, false, true),
                true
            )
        );

        // Get all configuration files
        $files = array_merge(
            $files,
            $this->_prepareFiles('config', \Magento\TestFramework\Utility\Files::init()->getConfigFiles())
        );

        //Get all layout updates files
        $files = array_merge(
            $files,
            $this->_prepareFiles('layout', \Magento\TestFramework\Utility\Files::init()->getLayoutFiles())
        );

        // Get all template files
        $files = array_merge(
            $files,
            $this->_prepareFiles('template', \Magento\TestFramework\Utility\Files::init()->getPhtmlFiles())
        );

        return $files;
    }

    /**
     * Prepare list of config.xml files (by modules)
     */
    protected static function _prepareListConfigXml()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('config.xml', array(), false);
        foreach ($files as $file) {
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                self::$_listConfigXml[$module] = $file;
            }
        }
    }

    /**
     * Prepare list of routes.xml files (by modules)
     */
    protected static function _prepareListRoutesXml()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('*/routes.xml', array(), false);
        foreach ($files as $file) {
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                self::$_listRoutesXml[$module][] = $file;
            }
        }
    }

    /**
     * Prepare map of routers
     */
    protected static function _prepareMapRouters()
    {
        $pattern = '/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)\/Controller\/' .
            '(?<path>[\/\w]*).php/';

        $files = \Magento\TestFramework\Utility\Files::init()->getPhpFiles(true, false, false, false);
        foreach ($files as $file) {
            if (preg_match($pattern, $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                if (!empty(self::$_listRoutesXml[$module])) {
                    foreach (self::$_listRoutesXml[$module] as $configFile) {
                        self::updateRoutersMap($module, $configFile);

                    }
                }
            }
        }
    }

    /**
     * Update routers map for the module basing on the routing config file
     *
     * @param string $module
     * @param string $configFile
     *
     * @return void
     */
    private static function updateRoutersMap($module, $configFile)
    {
        // Read module's routes.xml file
        $config = simplexml_load_file($configFile);
        $nodes  = $config->xpath("/config/router/*");
        foreach ($nodes as $node) {
            $id = (string)$node['id'];
            if ($id != 'adminhtml' && '' == (string)$node['frontName']) {
                // Exclude overridden routers
                continue;
            }
            if (!isset(self::$_mapRouters[$id])) {
                self::$_mapRouters[$id] = array();
            }
            if (!in_array($module, self::$_mapRouters[$id])) {
                self::$_mapRouters[$id][] = $module;
            }
        }
    }

    /**
     * Prepare map of layout blocks
     */
    protected static function _prepareMapLayoutBlocks()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getLayoutFiles(array(), false);
        foreach ($files as $file) {
            $area = 'default';
            if (preg_match('/[\/](?<area>adminhtml|frontend)[\/]/', $file, $matches)) {
                $area = $matches['area'];
                self::$_mapLayoutBlocks[$area] = @(self::$_mapLayoutBlocks[$area] ?: array());
            }
            if (preg_match('/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                $xml = simplexml_load_file($file);
                foreach ((array)$xml->xpath('//container | //block') as $element) {
                    /** @var \SimpleXMLElement $element */
                    $attributes = $element->attributes();
                    $block = (string)$attributes->name;
                    if (!empty($block)) {
                        self::$_mapLayoutBlocks[$area][$block] = @(self::$_mapLayoutBlocks[$area][$block] ?: array());
                        self::$_mapLayoutBlocks[$area][$block][$module] = $module;
                    }
                }
            }
        }
    }

    /**
     * Prepare map of layout handles
     */
    protected static function _prepareMapLayoutHandles()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getLayoutFiles(array(), false);
        foreach ($files as $file) {
            $area = 'default';
            if (preg_match('/\/(?<area>adminhtml|frontend)\//', $file, $matches)) {
                $area = $matches['area'];
                self::$_mapLayoutHandles[$area] = @(self::$_mapLayoutHandles[$area] ?: array());
            }
            if (preg_match('/app\/code\/(?<namespace>[A-Z][a-z]+)[_\/\\\\](?<module>[A-Z][a-zA-Z]+)/', $file, $matches)
            ) {
                $module = $matches['namespace'] . '\\' . $matches['module'];
                $xml = simplexml_load_file($file);
                foreach ((array)$xml->xpath('/layout/child::*') as $element) {
                    /** @var \SimpleXMLElement $element */
                    $handle = $element->getName();
                    self::$_mapLayoutHandles[$area][$handle] = @(self::$_mapLayoutHandles[$area][$handle] ?: array());
                    self::$_mapLayoutHandles[$area][$handle][$module] = $module;
                }
            }
        }
    }

    /**
     * Retrieve dependency types array
     *
     * @return array
     */
    protected static function _getTypes()
    {
        return array(self::TYPE_HARD, self::TYPE_SOFT);
    }

    /**
     * Initialise map of dependencies
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected static function _initDependencies()
    {
        $files = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('module.xml', array(), false);

        foreach ($files as $file) {
            $config = simplexml_load_file($file);
            $module = $config->xpath("/config/module");
            $moduleName = (string)$module[0]->attributes()->name;
            $moduleName = str_replace('_', '\\', $moduleName);
            self::$_mapDependencies[$moduleName] = @(self::$_mapDependencies[$moduleName] ?: array());

            foreach (self::_getTypes() as $type) {
                if (!isset(self::$_mapDependencies[$moduleName][$type])) {
                    self::$_mapDependencies[$moduleName][$type] = array(
                        self::MAP_TYPE_DECLARED => array(),
                        self::MAP_TYPE_FOUND => array(),
                        self::MAP_TYPE_REDUNDANT => array()
                    );
                }
            }

            if (isset($module[0]->depends)) {
                foreach ($module[0]->depends->children() as $dependency) {
                    /** @var \SimpleXMLElement $dependency */
                    $type = self::TYPE_HARD;
                    if (isset($dependency['type']) && (string)$dependency['type'] == self::TYPE_SOFT) {
                        $type = self::TYPE_SOFT;
                    }
                    if ($dependency->getName() == 'module') {
                        self::_addDependencies(
                            $moduleName,
                            $type,
                            self::MAP_TYPE_DECLARED,
                            str_replace('_', '\\', (string)$dependency->attributes()->name)
                        );
                    }
                }
            }
        }
    }

    /**
     * Add dependency map items
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @param $dependencies
     */
    protected static function _addDependencies($module, $type, $mapType, $dependencies)
    {
        if (!is_array($dependencies)) {
            $dependencies = array($dependencies);
        }
        foreach ($dependencies as $dependency) {
            if (isset(self::$_mapDependencies[$module][$type][$mapType])) {
                self::$_mapDependencies[$module][$type][$mapType][$dependency] = $dependency;
            }
        }
    }

    /**
     * Retrieve array of dependency items
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @return array
     */
    protected function _getDependencies($module, $type, $mapType)
    {
        if (isset(self::$_mapDependencies[$module][$type][$mapType])) {
            return self::$_mapDependencies[$module][$type][$mapType];
        }
        return array();
    }

    /**
     * Set dependency map items
     *
     * @param $module
     * @param $type
     * @param $mapType
     * @param $dependencies
     */
    protected function _setDependencies($module, $type, $mapType, $dependencies)
    {
        if (!is_array($dependencies)) {
            $dependencies = array($dependencies);
        }
        if (isset(self::$_mapDependencies[$module][$type][$mapType])) {
            self::$_mapDependencies[$module][$type][$mapType] = $dependencies;
        }
    }

    /**
     * Check if module is fake
     *
     * @param $module
     * @return bool
     */
    protected function _isFake($module)
    {
        return isset(self::$_mapDependencies[$module]) ? false : true;
    }
}
