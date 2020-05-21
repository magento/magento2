<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Test\Unit\Model\Quote;

use Magento\Quote\Model\Quote\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $_model;

    /**
     * @var MockObject
     */
    protected $_attributeConfig;

    protected function setUp(): void
    {
        $this->_attributeConfig = $this->createMock(\Magento\Catalog\Model\Attribute\Config::class);
        $this->_model = new Config($this->_attributeConfig);
    }

    public function testGetProductAttributes()
    {
        $attributes = ['attribute_one', 'attribute_two'];
        $this->_attributeConfig->expects(
            $this->once()
        )->method(
            'getAttributeNames'
        )->with(
            'quote_item'
        )->willReturn(
            $attributes
        );
        $this->assertEquals($attributes, $this->_model->getProductAttributes());
    }
}
