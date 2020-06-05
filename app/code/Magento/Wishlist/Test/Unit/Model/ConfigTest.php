<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Wishlist\Model\Config;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    protected $_scopeConfig;

    /**
     * @var \Magento\Catalog\Model\Config|MockObject
     */
    protected $_catalogConfig;

    /**
     * @var \Magento\Catalog\Model\Attribute\Config|MockObject
     */
    protected $_attributeConfig;

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->getMockBuilder(
            ScopeConfigInterface::class
        )->getMock();
        $this->_catalogConfig = $this->getMockBuilder(\Magento\Catalog\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->_attributeConfig = $this->getMockBuilder(\Magento\Catalog\Model\Attribute\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Config($this->_scopeConfig, $this->_catalogConfig, $this->_attributeConfig);
    }

    public function testGetProductAttributes()
    {
        $expectedResult = ['attribute_one', 'attribute_two', 'attribute_three'];

        $this->_catalogConfig->expects($this->once())
            ->method('getProductAttributes')
            ->willReturn(['attribute_one', 'attribute_two']);
        $this->_attributeConfig->expects($this->once())
            ->method('getAttributeNames')
            ->with('wishlist_item')
            ->willReturn(['attribute_three']);

        $this->assertEquals($expectedResult, $this->model->getProductAttributes());
    }

    public function testGetSharingEmailLimit()
    {
        $this->assertEquals(Config::SHARING_EMAIL_LIMIT, $this->model->getSharingEmailLimit());
    }

    public function testGetSharingTextLimit()
    {
        $this->assertEquals(Config::SHARING_TEXT_LIMIT, $this->model->getSharingTextLimit());
    }
}
