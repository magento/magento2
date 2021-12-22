<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp;

use Magento\Csp\Model\Collector\DynamicCollector;
use Magento\Csp\Model\Collector\DynamicCollectorMock;
use Magento\Framework\Math\Random;
use Magento\Framework\View\LayoutInterface;
use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Element\Template;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test that inline util works fine with cached blocks.
 */
class CachedBlockTest extends TestCase
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @var DynamicCollectorMock
     */
    private $dynamicCollected;

    /**
     * @var Random
     */
    private $random;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->configure([
            'preferences' => [
                DynamicCollector::class => DynamicCollectorMock::class
            ]
        ]);
        $this->layout = Bootstrap::getObjectManager()->get(LayoutInterface::class);
        $this->dynamicCollected = Bootstrap::getObjectManager()->get(DynamicCollector::class);
        $this->random = Bootstrap::getObjectManager()->get(Random::class);
    }

    /**
     * Validate policies preserved when reading block from cache.
     *
     * @return void
     *
     * @magentoAppArea frontend
     * @magentoCache block_html enabled
     */
    public function testCachedPolicies(): void
    {
        /** @var Template $block */
        $block = $this->layout->createBlock(
            Template::class,
            'test-block',
            ['data' => ['cache_lifetime' => 3600, 'cache_key' => $this->random->getRandomString(32)]]
        );
        $block->setTemplate('Magento_TestModuleCspUtil::secure.phtml');
        //Clearing previously added just in case.
        $this->dynamicCollected->consumeAdded();

        $block->toHtml();
        $dynamic = $this->dynamicCollected->consumeAdded();
        $this->assertNotEmpty($dynamic);

        //From cache
        $block->toHtml();
        $cached = $this->dynamicCollected->consumeAdded();
        $this->assertEquals($dynamic, $cached);
    }
}
