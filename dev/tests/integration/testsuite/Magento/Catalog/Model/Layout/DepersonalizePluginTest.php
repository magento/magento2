<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Layout;

use Magento\Catalog\Model\Session as CatalogSession;
use Magento\Framework\App\Cache\Type\Layout as LayoutCache;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Tests \Magento\Catalog\Model\Layout\DepersonalizePlugin.
 *
 * @magentoAppArea frontend
 */
class DepersonalizePluginTest extends TestCase
{
    /**
     * @var CatalogSession
     */
    private $catalogSession;

    /**
     * @var Layout
     */
    private $layout;

    /**
     * @var LayoutCache
     */
    private $cache;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->catalogSession = Bootstrap::getObjectManager()->get(CatalogSession::class);
        $this->layout = Bootstrap::getObjectManager()->get(LayoutFactory::class)->create();
        $this->cache = Bootstrap::getObjectManager()->get(LayoutCache::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->catalogSession->clearStorage();
    }

    /**
     * @magentoCache full_page enabled
     * @dataProvider afterGenerateElementsDataProvider
     *
     * @param string $layout
     * @param array $expectedResult
     * @return void
     */
    public function testAfterGenerateElements(string $layout, array $expectedResult): void
    {
        $this->cache->clean();
        $this->assertTrue($this->layout->loadFile($layout));
        $this->catalogSession->setData(['some_data' => 1]);
        $this->layout->generateElements();
        $this->assertEquals($expectedResult, $this->catalogSession->getData());
    }

    /**
     * @return array
     */
    public function afterGenerateElementsDataProvider(): array
    {
        return [
            'cacheable' => [
                'layout' => INTEGRATION_TESTS_DIR . '/testsuite/Magento/Framework/View/_files/layout/cacheable.xml',
                'expectedResult' => [],
            ],
            'nonCacheable' => [
                'layout' => INTEGRATION_TESTS_DIR . '/testsuite/Magento/Framework/View/_files/layout/non_cacheable.xml',
                'expectedResult' => ['some_data' => 1],
            ],
            'nonCacheableBlockWithoutReference' => [
                'layout' => INTEGRATION_TESTS_DIR
                . '/testsuite/Magento/Framework/View/_files/layout/non_cacheable_block_with_missing_refference.xml',
                'expectedResult' => [],
            ],
            'nonCacheableBlockWithExistedReference' => [
                'layout' => INTEGRATION_TESTS_DIR
                . '/testsuite/Magento/Framework/View/_files/layout/non_cacheable_block_with_declared_reference.xml',
                'expectedResult' => ['some_data' => 1],
            ],
        ];
    }
}
