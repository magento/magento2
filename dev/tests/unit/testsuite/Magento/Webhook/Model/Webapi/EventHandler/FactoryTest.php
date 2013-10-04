<?php
/**
 * \Magento\Webhook\Model\Webapi\EventHandler\Factory
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
 * @category    Magento
 * @package     Magento_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webhook\Model\Webapi\EventHandler;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $_mockObjectManager;

    /** @var \Magento\Webhook\Model\Webapi\EventHandler\Factory */
    private $_factory;

    protected function setUp()
    {
        $this->_mockObjectManager = $this->getMockBuilder('Magento\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_factory = new \Magento\Webhook\Model\Webapi\EventHandler\Factory($this->_mockObjectManager);
    }

    public function testCreate()
    {
        $mockEntity = $this->getMockBuilder('Magento\Webhook\Model\Webapi\EventHandler')
            ->disableOriginalConstructor()
            ->getMock();
        $this->_mockObjectManager->expects($this->once())
            ->method('create')
            ->with($this->equalTo('Magento\Webhook\Model\Webapi\EventHandler'), $this->equalTo(array()))
            ->will($this->returnValue($mockEntity));
        $this->assertSame($mockEntity, $this->_factory->create());
    }
}
