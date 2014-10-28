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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\SalesRule\Model;

/**
 * Class CouponTest
 */
class CouponTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $resourceMock;

    /**
     * @var \Magento\Framework\Event\Manager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $eventManager;

    /**
     * @var \Magento\SalesRule\Model\Coupon
     */
    protected $couponModel;

    protected function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->resourceMock = $this->getMock(
            'Magento\SalesRule\Model\Resource\Coupon',
            ['loadPrimaryByRule', 'load', '__wakeup', 'getIdFieldName'],
            [],
            '',
            false
        );
        $this->eventManager = $this->getMock(
            'Magento\Framework\Event\Manager',
            ['dispatch'],
            [],
            '',
            false
        );

        $context = $this->getMock(
            'Magento\Framework\Model\Context',
            ['getEventDispatcher'],
            [],
            '',
            false
        );

        $context->expects($this->once())->method('getEventDispatcher')->will($this->returnValue($this->eventManager));

        $this->couponModel = $objectManager->getObject(
            'Magento\SalesRule\Model\Coupon',
            [
                'resource' => $this->resourceMock,
                'context' => $context
            ]
        );
    }

    /**
     * Run test setRule method
     */
    public function testSetRule()
    {
        /** @var \Magento\SalesRule\Model\Rule|\PHPUnit_Framework_MockObject_MockObject $ruleMock */
        $ruleMock = $this->getMock('Magento\SalesRule\Model\Rule', ['getId', '__wakeup'], [], '', false);
        $ruleMock->expects($this->once())->method('getId');

        $this->assertEquals($this->couponModel, $this->couponModel->setRule($ruleMock));
    }

    /**
     * Run test loadPrimaryByRule method
     */
    public function testLoadPrimaryByRule()
    {
        $this->resourceMock->expects($this->once())->method('loadPrimaryByRule');

        $this->assertEquals($this->couponModel, $this->couponModel->loadPrimaryByRule(1));
    }

    /**
     * Run test loadByCode method
     */
    public function testLoadByCode()
    {
        $this->eventManager->expects($this->any())->method('dispatch');
        $this->resourceMock->expects($this->once())->method('load');

        $this->assertEquals($this->couponModel, $this->couponModel->loadByCode('code-value'));
    }
}
