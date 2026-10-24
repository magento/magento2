<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Product\Attribute\Backend;

use Magento\Catalog\Model\Product\Attribute\Backend\Url;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\CatalogUrlRewrite\Model\ProductScopeRewriteGenerator;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase
{
    /**
     * @var ScopeConfigInterface|MockObject
     */
    private ScopeConfigInterface $config;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $this->config = $this->createMock(ScopeConfigInterface::class);
        parent::setUp();
    }

    /**
     * @return void
     * @throws Exception
     */
    public function testSetAttribute(): void
    {
        $attribute = $this->createMock(Attribute::class);
        $attribute->expects($this->once())
            ->method('__call')
            ->with('setIsGlobal', [ScopedAttributeInterface::SCOPE_WEBSITE]);
        $this->config->expects($this->once())
            ->method('getValue')
            ->with(ProductScopeRewriteGenerator::URL_REWRITE_SCOPE_CONFIG_PATH, ScopeInterface::SCOPE_STORE)
            ->willReturn(ProductScopeRewriteGenerator::WEBSITE_URL_REWRITE_SCOPE);

        $url = new Url($this->config);
        $url->setAttribute($attribute);
    }
}
