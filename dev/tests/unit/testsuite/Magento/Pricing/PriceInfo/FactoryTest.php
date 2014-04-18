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
namespace Magento\Pricing\PriceInfo;

/**
 * Test class for \Magento\Pricing\PriceInfo\Factory
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

    /**
     * @dataProvider priceInfoClassesProvider
     */
    public function testCreate($types, $type, $expected)
    {
        $priceInfoFactory = $this->preparePriceInfoFactory(
            $expected,
            $types
        );

        $productMock = $this->getMock(
            'Magento\Catalog\Model\Product',
            ['getTypeId', 'getQty', '__wakeup'],
            [],
            '',
            false
        );

        $productMock->expects($this->any())
            ->method('getTypeId')
            ->will($this->returnValue($type));

        $productMock->expects($this->any())
            ->method('getQty')
            ->will($this->returnValue(1));

        $this->assertInstanceOf(
            $expected,
            $priceInfoFactory->create($productMock)
        );
    }

    /**
     * @param string $priceInfoInterface
     * @param array $types
     * @return object
     */
    protected function preparePriceInfoFactory($priceInfoInterface, $types = [])
    {
        return $this->objectManager->getObject(
            'Magento\Pricing\PriceInfo\Factory',
            [
                'types' => $types,
                'objectManager' => $this->prepareObjectManager($priceInfoInterface)
            ]
        );
    }

    /**
     * @param string $priceInfoInterface
     * @return \PHPUnit_Framework_MockObject_MockObject|\Magento\ObjectManager\ObjectManager
     */
    protected function prepareObjectManager($priceInfoInterface)
    {
        $objectManager = $this->getMock('Magento\ObjectManager\ObjectManager', ['create'], [], '', false);
        $objectManager->expects($this->any())
            ->method('create')
            ->will($this->returnValue($this->getMockForAbstractClass($priceInfoInterface)));
        return $objectManager;
    }

    /**
     * @return array
     */
    public function priceInfoClassesProvider()
    {
        return [
            [
                ['new_type' => 'Magento\Pricing\PriceInfo\Base'],
                'new_type',
                'Magento\Pricing\PriceInfoInterface'
            ],
            [
                [],
                'unknown',
                'Magento\Pricing\PriceInfoInterface'
            ]
        ];
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testCreateWithException()
    {
        $invalidPriceInfoInterface = 'Magento\Object';
        $priceInfoFactory = $this->preparePriceInfoFactory($invalidPriceInfoInterface);
        $priceInfoFactory->create(
            $this->getMock('Magento\Catalog\Model\Product', ['__wakeup'], [], '', false)
        );
    }
}
