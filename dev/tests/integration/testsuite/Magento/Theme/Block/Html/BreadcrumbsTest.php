<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea frontend
 */
class BreadcrumbsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Breadcrumbs
     */
    private $block;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    protected function setUp(): void
    {
        Bootstrap::getObjectManager()->get(\Magento\Framework\App\State::class)->setAreaCode('frontend');
        $this->block = Bootstrap::getObjectManager()
            ->get(\Magento\Framework\View\LayoutInterface::class)
            ->createBlock(\Magento\Theme\Block\Html\Breadcrumbs::class);
        $this->serializer = Bootstrap::getObjectManager()->get(SerializerInterface::class);
    }

    public function testAddCrumb()
    {
        $this->assertEmpty($this->block->toHtml());
        $info = ['label' => 'test label', 'title' => 'test title', 'link' => 'test link'];
        $this->block->addCrumb('test', $info);
        $html = $this->block->toHtml();
        $this->assertStringContainsString('test label', $html);
        $this->assertStringContainsString('test&#x20;title', $html);
        $this->assertStringContainsString('test link', $html);
    }

    public function testGetCacheKeyInfo()
    {
        $crumbs = ['test' => ['label' => 'test label', 'title' => 'test title', 'link' => 'test link']];
        foreach ($crumbs as $crumbName => &$crumb) {
            $this->block->addCrumb($crumbName, $crumb);
            $crumb += ['first' => null, 'last' => null, 'readonly' => null];
        }

        $cacheKeyInfo = $this->block->getCacheKeyInfo();
        $crumbsFromCacheKey = $this->serializer->unserialize(base64_decode($cacheKeyInfo['crumbs']));
        $this->assertEquals($crumbs, $crumbsFromCacheKey);
    }
}
