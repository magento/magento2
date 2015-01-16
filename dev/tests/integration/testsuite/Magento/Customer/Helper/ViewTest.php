<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Helper;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\TestFramework\Helper\Bootstrap;

class ViewTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Customer\Helper\View */
    protected $_helper;

    /** @var CustomerMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $_customerMetadataService;

    protected function setUp()
    {
        $this->_customerMetadataService = $this->getMock('Magento\Customer\Api\CustomerMetadataInterface');
        $this->_helper = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Helper\View',
            ['customerMetadataService' => $this->_customerMetadataService]
        );
        parent::setUp();
    }

    /**
     * @param \Magento\Customer\Api\Data\CustomerInterface $customerData
     * @param string $expectedCustomerName
     * @param bool $isPrefixAllowed
     * @param bool $isMiddleNameAllowed
     * @param bool $isSuffixAllowed
     * @dataProvider getCustomerNameDataProvider
     */
    public function testGetCustomerName(
        $customerData,
        $expectedCustomerName,
        $isPrefixAllowed = false,
        $isMiddleNameAllowed = false,
        $isSuffixAllowed = false
    ) {
        $visibleAttribute = $this->getMock('Magento\Customer\Api\Data\AttributeMetadataInterface');
        $visibleAttribute->expects($this->any())->method('isVisible')->will($this->returnValue(true));

        $invisibleAttribute = $this->getMock('Magento\Customer\Api\Data\AttributeMetadataInterface');
        $invisibleAttribute->expects($this->any())->method('isVisible')->will($this->returnValue(false));

        $this->_customerMetadataService->expects(
            $this->any()
        )->method(
            'getAttributeMetadata'
        )->will(
            $this->returnValueMap(
                [
                    ['prefix', $isPrefixAllowed ? $visibleAttribute : $invisibleAttribute],
                    ['middlename', $isMiddleNameAllowed ? $visibleAttribute : $invisibleAttribute],
                    ['suffix', $isSuffixAllowed ? $visibleAttribute : $invisibleAttribute],
                ]
            )
        );

        $this->assertEquals(
            $expectedCustomerName,
            $this->_helper->getCustomerName($customerData),
            'Full customer name is invalid'
        );
    }

    public function getCustomerNameDataProvider()
    {
        /** @var \Magento\Customer\Api\Data\CustomerDataBuilder $customerBuilder */
        $customerBuilder = Bootstrap::getObjectManager()->create('Magento\Customer\Api\Data\CustomerDataBuilder');
        return [
            'With disabled prefix, middle name, suffix' => [
                $customerBuilder->setPrefix(
                    'prefix'
                )->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->setSuffix(
                    'suffix'
                )->create(),
                'FirstName LastName',
            ],
            'With prefix, middle name, suffix' => [
                $customerBuilder->setPrefix(
                    'prefix'
                )->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->setSuffix(
                    'suffix'
                )->create(),
                'prefix FirstName MiddleName LastName suffix',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true, //$isSuffixAllowed
            ],
            'Empty prefix, middle name, suffix' => [
                $customerBuilder->setFirstname('FirstName')->setLastname('LastName')->create(),
                'FirstName LastName',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true, //$isSuffixAllowed
            ],
            'Empty prefix and suffix, not empty middle name' => [
                $customerBuilder->setFirstname(
                    'FirstName'
                )->setMiddlename(
                    'MiddleName'
                )->setLastname(
                    'LastName'
                )->create(),
                'FirstName MiddleName LastName',
                true, // $isPrefixAllowed
                true, // $isMiddleNameAllowed
                true, //$isSuffixAllowed
            ]
        ];
    }
}
