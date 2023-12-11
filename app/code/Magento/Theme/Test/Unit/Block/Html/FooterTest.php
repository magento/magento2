<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Test\Unit\Block\Html;

use Magento\Cms\Model\Block;
use Magento\Framework\App\Config;
use Magento\Framework\Escaper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Theme\Block\Html\Footer;
use Magento\Theme\Block\Html\Header;
use PHPUnit\Framework\TestCase;

class FooterTest extends TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Footer
     */
    protected $block;

    /**
     * @var Config
     */
    private $scopeConfig;
    
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);

        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getScopeConfig'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->scopeConfig = $this->getMockBuilder(Config::class)
            ->setMethods(['getValue'])
            ->disableOriginalConstructor()
            ->getMock();
        $context->expects($this->once())->method('getScopeConfig')->willReturn($this->scopeConfig);

        $this->block = $objectManager->getObject(
            Footer::class,
            [
                'context' => $context,
            ]
        );
    }

    protected function tearDown(): void
    {
        $this->block = null;
    }

    public function testGetCopyright()
    {
        $this->scopeConfig->expects($this->once())->method('getValue')
            ->with('design/footer/copyright', ScopeInterface::SCOPE_STORE)
            ->willReturn('Copyright 2013-{YYYY}');

        $this->assertEquals(
            'Copyright 2013-' . date('Y'),
            $this->block->getCopyright()
        );
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
