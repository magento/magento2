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
 */
namespace Magento\Test\Integrity;

use Magento\TestFramework\Utility\Files;
use Magento\Tools\Dependency\Circular;

class CircularDependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Modules dependencies map
     *
     * @var array
     */
    protected $moduleDependencies = array();

    /**
     * Circular dependencies
     *
     * @var array
     */
    protected $circularModuleDependencies = array();

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
        $configFiles = Files::init()->getConfigFiles('module.xml', array(), false);

        foreach ($configFiles as $configFile) {
            preg_match('#/([^/]+?/[^/]+?)/etc/module\.xml$#', $configFile, $moduleName);
            $moduleName = str_replace('/', '_', $moduleName[1]);
            $config = simplexml_load_file($configFile);
            $result = $config->xpath("/config/module/depends/module") ?: array();
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
