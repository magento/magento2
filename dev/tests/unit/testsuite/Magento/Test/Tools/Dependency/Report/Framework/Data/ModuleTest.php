<?php
/**
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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Test\Tools\Dependency\Report\Framework\Data;

use Magento\TestFramework\Helper\ObjectManager;

class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $name
     * @param array $dependencies
     * @return \Magento\Tools\Dependency\Report\Framework\Data\Module
     */
    protected function createModule($name, $dependencies = array())
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Framework\Data\Module',
            array('name' => $name, 'dependencies' => $dependencies)
        );
    }

    public function testGetName()
    {
        $name = 'name';
        $module = $this->createModule($name, array());

        $this->assertEquals($name, $module->getName());
    }

    public function testGetDependencies()
    {
        $dependencies = array('foo', 'baz', 'bar');
        $module = $this->createModule('name', $dependencies);

        $this->assertEquals($dependencies, $module->getDependencies());
    }

    public function testGetDependenciesCount()
    {
        $module = $this->createModule('name', array('foo', 'baz', 'bar'));

        $this->assertEquals(3, $module->getDependenciesCount());
    }
}
