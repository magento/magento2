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
 * @category    tests
 * @package     static
 * @subpackage  Integrity
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Integrity;

class CircularDependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Modules dependencies map
     *
     * @var array
     */
    protected $_moduleDependencies = array();

    /**
     * Circular dependencies
     *
     * @var array
     */
    protected $_circularDepends = array();

    /**
     * Build modules dependencies
     */
    protected function _buildModulesDependencies()
    {
        if (!empty($this->_moduleDependencies)) {
            return true;
        }
        $configFiles = \Magento\TestFramework\Utility\Files::init()->getConfigFiles('module.xml', array(), false);

        foreach ($configFiles as $configFile) {
            preg_match('#/([^/]+?/[^/]+?)/etc/module\.xml$#', $configFile, $moduleName);
            $moduleName = str_replace('/', '_', $moduleName[1]);
            $config = simplexml_load_file($configFile);
            $result = $config->xpath("/config/module/depends/module") ?: array();
            while (list( , $node) = each($result)) {
                /** @var \SimpleXMLElement $node */
                $this->_moduleDependencies[$moduleName][] = (string)$node['name'];
            }
        }

        $graph = new \Magento\Data\Graph(array_keys($this->_moduleDependencies), array());

        foreach (array_keys($this->_moduleDependencies) as $module) {
            $this->_expandDependencies($module, $graph);
        }
        $circulars  = $graph->findCycle(null, false);
        foreach ($circulars as $circular) {
            array_shift($circular);
            $this->_buildCircular($circular);
        }
    }

    /**
     * Expand modules dependencies from modules chain
     *
     * @param string $module
     * @param \Magento\Data\Graph $graph
     * @param string $path nesting path
     */
    protected function _expandDependencies($module, \Magento\Data\Graph $graph, $path = '')
    {
        if (empty($this->_moduleDependencies[$module])) {
            return;
        }

        $path .= '/' . $module;
        foreach ($this->_moduleDependencies[$module] as $dependency) {
            $relations = $graph->getRelations();
            if (isset($relations[$module][$dependency])) {
                continue;
            }
            $graph->addRelation($module, $dependency);

            $modulesChain = explode('/', $path);
            $searchResult = array_search($dependency, $modulesChain);

            if (false !== $searchResult) {
                $this->_buildCircular(array_slice($modulesChain, $searchResult));
                return;
            } else {
                $this->_expandDependencies($dependency, $graph, $path);
            }
        }
    }

    /**
     * Build all circular dependencies based on chain
     *
     * @param array $modules
     */
    protected function _buildCircular($modules)
    {
        $path = '/' . implode('/', $modules);
        if (isset($this->_circularDepends[$path])) {
            return;
        }
        $this->_circularDepends[$path] = $modules;
        array_push($modules, array_shift($modules));
        $this->_buildCircular($modules);
    }

    /**
     * Check Magento modules structure for circular dependencies
     */
    public function testCircularDependencies()
    {
        $this->markTestSkipped('Skipped before circular dependencies will be fixed MAGETWO-10938');
        $dependenciesByModule = array();
        $result = '';
        $this->_buildModulesDependencies();
        if (!empty($this->_circularDepends)) {
            foreach ($this->_circularDepends as $circularDependency) {
                $module =  array_shift($circularDependency);
                array_push($circularDependency, $module);
                $dependenciesByModule[$module][] = $circularDependency;

            }
        }

        foreach ($dependenciesByModule as $module => $moduleCircular) {
            $result .= "$module dependencies:" . PHP_EOL;
            foreach ($moduleCircular as $chain) {
                $result .= "Chain : " . implode('->', $chain) . PHP_EOL;
            }
            $result .= PHP_EOL;
        }
        $this->fail("Circular dependencies:" . PHP_EOL. $result);
    }
}
