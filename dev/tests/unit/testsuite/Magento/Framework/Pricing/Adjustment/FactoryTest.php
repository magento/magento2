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
namespace Magento\Framework\Pricing\Adjustment;

/**
 * Test class for \Magento\Framework\Pricing\Adjustment\Factory
 */
class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $objectManager;

    public function setUp()
    {
        $this->objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testCreate()
    {
        $adjustmentInterface = 'Magento\Framework\Pricing\Adjustment\AdjustmentInterface';
        $adjustmentFactory = $this->prepareAdjustmentFactory($adjustmentInterface);

        $this->assertInstanceOf(
            $adjustmentInterface,
            $adjustmentFactory->create($adjustmentInterface)
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return object
     */
    protected function prepareAdjustmentFactory($adjustmentInterface)
    {
        return $this->objectManager->getObject(
            'Magento\Framework\Pricing\Adjustment\Factory',
            ['objectManager' => $this->prepareObjectManager($adjustmentInterface)]
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\Framework\ObjectManager\ObjectManager
     */
    protected function prepareObjectManager($adjustmentInterface)
    {
        $objectManager = $this->getMock(
            'Magento\Framework\ObjectManager\ObjectManager',
            ['create'],
            [],
            '',
            false
        );
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMockForAbstractClass($adjustmentInterface)));
        return $objectManager;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
        $invalidAdjustmentInterface = 'Magento\Framework\Object';
        $adjustmentFactory = $this->prepareAdjustmentFactory($invalidAdjustmentInterface);
        $adjustmentFactory->create($invalidAdjustmentInterface);
    }
}
