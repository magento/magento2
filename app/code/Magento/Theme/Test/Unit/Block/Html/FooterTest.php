<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Cms\Model\Block;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Store\Model\Store;
use Magento\Theme\Block\Html\Footer;
use PHPUnit\Framework\TestCase;

class FooterTest extends TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Footer
     */
    protected $block;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->block = $objectManager->getObject(Footer::class);
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetIdentities()
    {
        $this->assertEquals(
            [Store::CACHE_TAG, Block::CACHE_TAG],
            $this->block->getIdentities()
        );
    }

    /**
     * Check Footer block has cache lifetime.
     *
     * @throws \ReflectionException
     * @return void
     */
    public function testGetCacheLifetime()
    {
        $reflection = new \ReflectionClass($this->block);
        $method = $reflection->getMethod('getCacheLifetime');
        $method->setAccessible(true);
        $this->assertEquals(3600, $method->invoke($this->block));
    }
}
