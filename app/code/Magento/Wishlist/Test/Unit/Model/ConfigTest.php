<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Wishlist\Test\Unit\Model;

use Magento\Catalog\Model\Attribute\Config as AttributeConfig;
use Magento\Catalog\Model\Config as CatalogConfig;
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
     * @var CatalogConfig|MockObject
     */
    protected $_catalogConfig;

    /**
     * @var AttributeConfig|MockObject
     */
    protected $_attributeConfig;

    protected function setUp(): void
    {
        $this->_scopeConfig = $this->createMock(ScopeConfigInterface::class);
        $this->_catalogConfig = $this->createMock(CatalogConfig::class);
        $this->_attributeConfig = $this->createMock(AttributeConfig::class);

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
