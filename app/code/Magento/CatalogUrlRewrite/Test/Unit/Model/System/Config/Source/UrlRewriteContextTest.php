<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Test\Unit\Model\System\Config\Source;

use Magento\CatalogUrlRewrite\Model\System\Config\Source\UrlRewriteContext;
use PHPUnit\Framework\TestCase;

class UrlRewriteContextTest extends TestCase
{
    /**
     * @return void
     */
    public function testToOptionArray(): void
    {
        $contextRewriteOptions = new UrlRewriteContext();
        $this->assertEquals(
            [
                ['value' => 'website', 'label' => __('Website')],
                ['value' => 'store_view', 'label' => __('Store View')]
            ],
            $contextRewriteOptions->toOptionArray()
        );
    }
}
