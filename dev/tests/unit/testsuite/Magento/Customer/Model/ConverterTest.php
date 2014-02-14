<?php
/**
 * Unit test for converter \Magento\Customer\Model\Converter
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
namespace Magento\Customer\Model;

class ConverterTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCustomerFromModel()
    {
        $customerModelMock =
            $this->getMockBuilder('Magento\Customer\Model\Customer')
                ->disableOriginalConstructor()
                ->setMethods(
                    array(
                        'getId',
                        'getFirstname',
                        'getLastname',
                        'getEmail',
                        'getAttributes',
                        'getData',
                        '__wakeup',
                    )
                )
                ->getMock();

        $attributeModelMock =
            $this->getMockBuilder('\Magento\Customer\Model\Attribute')
                ->disableOriginalConstructor()
                ->getMock();

        $attributeModelMock
            ->expects($this->at(0))
            ->method('getAttributeCode')
            ->will($this->returnValue('attribute_code'));

        $attributeModelMock
            ->expects($this->at(1))
            ->method('getAttributeCode')
            ->will($this->returnValue('attribute_code2'));

        $attributeModelMock
            ->expects($this->at(2))
            ->method('getAttributeCode')
            ->will($this->returnValue('attribute_code3'));

        $this->_mockReturnValue(
            $customerModelMock,
            array(
                'getId' => 1,
                'getFirstname' => 'Tess',
                'getLastname' => 'Tester',
                'getEmail' => 'ttester@example.com',
                'getAttributes' => [$attributeModelMock, $attributeModelMock, $attributeModelMock],
            )
        );

        $map = [
            ['attribute_code', null, 'attributeValue'],
            ['attribute_code2', null, 'attributeValue2'],
            ['attribute_code3', null, null],
        ];
        $customerModelMock
            ->expects($this->any())
            ->method('getData')
            ->will($this->returnValueMap($map));

        $customerBuilder = new \Magento\Customer\Service\V1\Dto\CustomerBuilder();
        $customerFactory = $this->getMockBuilder('Magento\Customer\Model\CustomerFactory')
            ->disableOriginalConstructor()
            ->getMock();

        $converter = new Converter($customerBuilder, $customerFactory);
        $customerDto = $converter->createCustomerFromModel($customerModelMock);

        $customerBuilder = new \Magento\Customer\Service\V1\Dto\CustomerBuilder();
        $customerData = [
            'firstname' => 'Tess',
            'email' => 'ttester@example.com',
            'lastname' => 'Tester',
            'id' => 1,
            'attribute_code' => 'attributeValue',
            'attribute_code2' => 'attributeValue2'
        ];
        // There will be no attribute_code3: it has a value of null, so the converter will drop it
        $customerBuilder->populateWithArray($customerData);
        $expectedCustomerDto = $customerBuilder->create();

        $this->assertEquals($expectedCustomerDto, $customerDto);
    }

    /**
     * @param \PHPUnit_Framework_MockObject_MockObject $mock
     * @param array $valueMap
     */
    private function _mockReturnValue(\PHPUnit_Framework_MockObject_MockObject $mock, $valueMap)
    {
        foreach ($valueMap as $method => $value) {
            $mock->expects($this->any())
                ->method($method)
                ->will($this->returnValue($value));
        }
    }
}
