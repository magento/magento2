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
namespace Magento\Test\Tools\Dependency\Report\Dependency\Data;

use Magento\TestFramework\Helper\ObjectManager;
use Magento\Tools\Dependency\Report\Dependency\Data\Dependency;

class DependencyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param string $module
     * @param string|null $type One of \Magento\Tools\Dependency\Dependency::TYPE_ const
     * @return \Magento\Tools\Dependency\Report\Dependency\Data\Dependency
     */
    protected function createDependency($module, $type = null)
    {
        $objectManagerHelper = new ObjectManager($this);
        return $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Dependency\Data\Dependency',
            array('module' => $module, 'type' => $type)
        );
    }

    public function testGetModule()
    {
        $module = 'module';

        $dependency = $this->createDependency($module);

        $this->assertEquals($module, $dependency->getModule());
    }

    public function testGetType()
    {
        $type = Dependency::TYPE_SOFT;

        $dependency = $this->createDependency('module', $type);

        $this->assertEquals($type, $dependency->getType());
    }

    public function testThatHardTypeIsDefault()
    {
        $dependency = $this->createDependency('module');

        $this->assertEquals(Dependency::TYPE_HARD, $dependency->getType());
    }

    public function testThatHardTypeIsDefaultIfPassedWrongType()
    {
        $dependency = $this->createDependency('module', 'wrong_type');

        $this->assertEquals(Dependency::TYPE_HARD, $dependency->getType());
    }
}
