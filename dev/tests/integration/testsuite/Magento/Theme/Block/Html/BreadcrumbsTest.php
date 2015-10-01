<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

/**
 * @magentoAppArea frontend
 */
class BreadcrumbsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Breadcrumbs
     */
    protected $_block;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Theme\Block\Html\Breadcrumbs'
        );
    }

    public function testAddCrumb()
    {
        $this->assertEmpty($this->_block->toHtml());
        $info = ['label' => 'test label', 'title' => 'test title', 'link' => 'test link'];
        $this->_block->addCrumb('test', $info);
        $html = $this->_block->toHtml();
        $this->assertContains('test label', $html);
        $this->assertContains('test title', $html);
        $this->assertContains('test link', $html);
    }

    public function testGetCacheKeyInfo()
    {
        $crumbs = ['test' => ['label' => 'test label', 'title' => 'test title', 'link' => 'test link']];
        foreach ($crumbs as $crumbName => &$crumb) {
            $this->_block->addCrumb($crumbName, $crumb);
            $crumb += ['first' => null, 'last' => null, 'readonly' => null];
        }

        $cacheKeyInfo = $this->_block->getCacheKeyInfo();
        $crumbsFromCacheKey = unserialize(base64_decode($cacheKeyInfo['crumbs']));
        $this->assertEquals($crumbs, $crumbsFromCacheKey);
    }
}
