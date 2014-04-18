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
 * @category    Magento
 * @package     Magento_Pricing
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Pricing\Adjustment;

/**
 * Test class for \Magento\Pricing\Adjustment\Factory
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
        $adjustmentInterface = 'Magento\Pricing\Adjustment\AdjustmentInterface';
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
            'Magento\Pricing\Adjustment\Factory',
            ['objectManager' => $this->prepareObjectManager($adjustmentInterface)]
        );
    }

    /**
     * @param string $adjustmentInterface
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\ObjectManager\ObjectManager
     */
    protected function prepareObjectManager($adjustmentInterface)
    {
        $objectManager = $this->getMock('Magento\ObjectManager\ObjectManager', array('create'), array(), '', false);
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
        $invalidAdjustmentInterface = 'Magento\Object';
        $adjustmentFactory = $this->prepareAdjustmentFactory($invalidAdjustmentInterface);
        $adjustmentFactory->create($invalidAdjustmentInterface);
    }
}
