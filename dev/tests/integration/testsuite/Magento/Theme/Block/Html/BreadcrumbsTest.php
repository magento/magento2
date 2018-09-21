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

    protected function setUp()
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
        $this->assertContains('test label', $html);
        $this->assertContains('test title', $html);
        $this->assertContains('test link', $html);
    }

    public function testGetCrumb()
    {
        $this->assertEmpty($this->block->toHtml());
        $this->assertEquals($this->block->getCrumbs(), []);
        $info = ['label' => '1', 'title' => '1', 'link' => '1', 'first' => null, 'last' => null, 'readonly' => null];
        $this->block->addCrumb('test', $info);
        $html = $this->block->toHtml();
        $this->assertEquals($this->block->getCrumbs(), $info);
    }

    public function testAddCrumbAfterExisting()
    {
        $this->assertEmpty($this->block->toHtml());
        $info1 = ['label' => '1', 'title' => '1', 'link' => '1', 'first' => null, 'last' => null, 'readonly' => null];
        $info2 = ['label' => '2', 'title' => '2', 'link' => '2', 'first' => null, 'last' => null, 'readonly' => null];
        $info3 = ['label' => '3', 'title' => '3', 'link' => '3', 'first' => null, 'last' => null, 'readonly' => null];
        $this->block->addCrumb('test1', $info1);
        $this->block->addCrumb('test2', $info2);
        $this->block->addCrumbAfter('test3', $info3, 'test1');
        $html = $this->block->toHtml();
        $this->assertEquals($this->block->getCrumbs(), $info1 + $info3 + $info2);
    }

    public function testAddCrumbAfterNonExisting()
    {
        $this->assertEmpty($this->block->toHtml());
        $info1 = ['label' => '1', 'title' => '1', 'link' => '1', 'first' => null, 'last' => null, 'readonly' => null];
        $info2 = ['label' => '2', 'title' => '2', 'link' => '2', 'first' => null, 'last' => null, 'readonly' => null];
        $info3 = ['label' => '3', 'title' => '3', 'link' => '3', 'first' => null, 'last' => null, 'readonly' => null];
        $this->block->addCrumb('test1', $info1);
        $this->block->addCrumb('test2', $info2);
        $this->block->addCrumbAfter('test3', $info3, 'na');
        $html = $this->block->toHtml();
        $this->assertEquals($this->block->getCrumbs(), $info1 + $info2 + $info3);
    }

    public function testAddCrumbBeforeExisting()
    {
        $this->assertEmpty($this->block->toHtml());
        $info1 = ['label' => '1', 'title' => '1', 'link' => '1', 'first' => null, 'last' => null, 'readonly' => null];
        $info2 = ['label' => '2', 'title' => '2', 'link' => '2', 'first' => null, 'last' => null, 'readonly' => null];
        $info3 = ['label' => '3', 'title' => '3', 'link' => '3', 'first' => null, 'last' => null, 'readonly' => null];
        $this->block->addCrumb('test1', $info1);
        $this->block->addCrumb('test2', $info2);
        $this->block->addCrumbBefore('test3', $info3, 'test2');
        $html = $this->block->toHtml();
        $this->assertEquals($this->block->getCrumbs(), $info1 + $info3 + $info1);
    }

    public function testAddCrumbBeforeNonExisting()
    {
        $this->assertEmpty($this->block->toHtml());
        $info1 = ['label' => '1', 'title' => '1', 'link' => '1', 'first' => null, 'last' => null, 'readonly' => null];
        $info2 = ['label' => '2', 'title' => '2', 'link' => '2', 'first' => null, 'last' => null, 'readonly' => null];
        $info3 = ['label' => '3', 'title' => '3', 'link' => '3', 'first' => null, 'last' => null, 'readonly' => null];
        $this->block->addCrumb('test1', $info1);
        $this->block->addCrumb('test2', $info2);
        $this->block->addCrumbBefore('test3', $info3, 'na');
        $html = $this->block->toHtml();
        $this->assertEquals($this->block->getCrumbs(), $info1 + $info2 + $info3);
    }

    public function testRemoveCrumb()
    {
        $this->assertEmpty($this->block->toHtml());
        $info = ['label' => 'test label', 'title' => 'test title', 'link' => 'test link'];
        $this->block->addCrumb('test', $info);
        $html = $this->block->toHtml();
        $this->block->removeCrumb('test');
        $this->assertEmpty($this->block->toHtml());
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
