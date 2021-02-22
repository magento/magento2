<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Model\Attribute;

class ConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Catalog\Model\Attribute\Config
     */
    protected $_model;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $_dataStorage;

    protected function setUp(): void
    {
        $this->_dataStorage = $this->createPartialMock(\Magento\Catalog\Model\Attribute\Config\Data::class, ['get']);
        $this->_model = new \Magento\Catalog\Model\Attribute\Config($this->_dataStorage);
    }

    public function testGetAttributeNames()
    {
        $expectedResult = ['fixture_attribute_one', 'fixture_attribute_two'];
        $this->_dataStorage->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            'some_group'
        )->willReturn(
            $expectedResult
        );
        $this->assertSame($expectedResult, $this->_model->getAttributeNames('some_group'));
    }
}
