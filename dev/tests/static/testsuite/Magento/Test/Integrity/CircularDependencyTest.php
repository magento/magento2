<?php
/**
 * Scan source code for incorrect or undeclared modules dependencies
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity;

use Magento\Framework\Test\Utility\Files;
use Magento\Tools\Dependency\Circular;

class CircularDependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Modules dependencies map
     *
     * @var array
     */
    protected $moduleDependencies = [];

    /**
     * Circular dependencies
     *
     * @var array
     */
    protected $circularModuleDependencies = [];

    public function setUp()
    {
        $this->buildModulesDependencies();
        $this->buildCircularModulesDependencies();
    }

    /**
     * Build modules dependencies
     */
    protected function buildModulesDependencies()
    {
        $configFiles = Files::init()->getConfigFiles('module.xml', [], false);

        foreach ($configFiles as $configFile) {
            preg_match('#/([^/]+?/[^/]+?)/etc/module\.xml$#', $configFile, $moduleName);
            $moduleName = str_replace('/', '_', $moduleName[1]);
            $config = simplexml_load_file($configFile);
            $result = $config->xpath("/config/module/depends/module") ?: [];
            while (list(, $node) = each($result)) {
                /** @var \SimpleXMLElement $node */
                $this->moduleDependencies[$moduleName][] = (string)$node['name'];
            }
        }
    }

    /**
     * Build circular modules dependencies
     */
    protected function buildCircularModulesDependencies()
    {
        $this->circularModuleDependencies = (new Circular())->buildCircularDependencies($this->moduleDependencies);
    }

    /**
     * Check Magento modules structure for circular dependencies
     */
    public function testCircularDependencies()
    {
        $this->markTestSkipped('Skipped before circular dependencies will be fixed MAGETWO-10938');
        if ($this->circularModuleDependencies) {
            $result = '';
            foreach ($this->circularModuleDependencies as $module => $chains) {
                $result .= $module . ' dependencies:' . PHP_EOL;
                foreach ($chains as $chain) {
                    $result .= 'Chain : ' . implode('->', $chain) . PHP_EOL;
                }
                $result .= PHP_EOL;
            }
            $this->fail('Circular dependencies:' . PHP_EOL . $result);
        }
    }
}
