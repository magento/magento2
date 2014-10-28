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
namespace Magento\Test\Tools\Dependency\Report\Circular\Data;

use Magento\TestFramework\Helper\ObjectManager;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Tools\Dependency\Report\Circular\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleFirst;

    /**
     * @var \Magento\Tools\Dependency\Report\Circular\Data\Module|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $moduleSecond;

    /**
     * @var \Magento\Tools\Dependency\Report\Circular\Data\Config
     */
    protected $config;

    public function setUp()
    {
        $this->moduleFirst = $this->getMock(
            'Magento\Tools\Dependency\Report\Circular\Data\Module',
            array(),
            array(),
            '',
            false
        );
        $this->moduleSecond = $this->getMock(
            'Magento\Tools\Dependency\Report\Circular\Data\Module',
            array(),
            array(),
            '',
            false
        );

        $objectManagerHelper = new ObjectManager($this);
        $this->config = $objectManagerHelper->getObject(
            'Magento\Tools\Dependency\Report\Circular\Data\Config',
            array('modules' => array($this->moduleFirst, $this->moduleSecond))
        );
    }

    public function testGetDependenciesCount()
    {
        $this->moduleFirst->expects($this->once())->method('getChainsCount')->will($this->returnValue(0));
        $this->moduleSecond->expects($this->once())->method('getChainsCount')->will($this->returnValue(2));

        $this->assertEquals(2, $this->config->getDependenciesCount());
    }
}
