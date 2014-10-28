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
namespace Magento\Tax\Model\TaxClass;

class FactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider createDataProvider
     *
     * @param string $classType
     * @param string $className
     * @param \PHPUnit_Framework_MockObject_MockObject $classTypeMock
     */
    public function testCreate($classType, $className, $classTypeMock)
    {
        $classMock = $this->getMock(
            'Magento\Tax\Model\ClassModel',
            array('getClassType', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $classMock->expects($this->once())->method('getClassType')->will($this->returnValue($classType));
        $classMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $objectManager = $this->getMock('Magento\Framework\ObjectManager', array(), array('create'), '', false);
        $objectManager->expects(
            $this->once()
        )->method(
            'create'
        )->with(
            $this->equalTo($className),
            $this->equalTo(array('data' => array('id' => 1)))
        )->will(
            $this->returnValue($classTypeMock)
        );

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);
        $this->assertEquals($classTypeMock, $taxClassFactory->create($classMock));
    }

    public function createDataProvider()
    {
        $customerClassMock = $this->getMock('Magento\Tax\Model\TaxClass\Type\Customer', array(), array(), '', false);
        $productClassMock = $this->getMock('Magento\Tax\Model\TaxClass\Type\Product', array(), array(), '', false);
        return array(
            array(
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_CUSTOMER,
                'Magento\Tax\Model\TaxClass\Type\Customer',
                $customerClassMock
            ),
            array(
                \Magento\Tax\Model\ClassModel::TAX_CLASS_TYPE_PRODUCT,
                'Magento\Tax\Model\TaxClass\Type\Product',
                $productClassMock
            )
        );
    }

    public function testCreateWithWrongClassType()
    {
        $wrongClassType = 'TYPE';
        $classMock = $this->getMock(
            'Magento\Tax\Model\ClassModel',
            array('getClassType', 'getId', '__wakeup'),
            array(),
            '',
            false
        );
        $classMock->expects($this->once())->method('getClassType')->will($this->returnValue($wrongClassType));

        $objectManager = $this->getMock('Magento\Framework\ObjectManager', array(), array(), '', false);

        $taxClassFactory = new \Magento\Tax\Model\TaxClass\Factory($objectManager);

        $this->setExpectedException(
            'Magento\Framework\Model\Exception',
            sprintf('Invalid type of tax class "%s"', $wrongClassType)
        );
        $taxClassFactory->create($classMock);
    }
}
