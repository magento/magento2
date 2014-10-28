<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Theme\Block\Html;

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
        $info = array('label' => 'test label', 'title' => 'test title', 'link' => 'test link');
        $this->_block->addCrumb('test', $info);
        $html = $this->_block->toHtml();
        $this->assertContains('test label', $html);
        $this->assertContains('test title', $html);
        $this->assertContains('test link', $html);
    }

    public function testGetCacheKeyInfo()
    {
        $crumbs = array('test' => array('label' => 'test label', 'title' => 'test title', 'link' => 'test link'));
        foreach ($crumbs as $crumbName => &$crumb) {
            $this->_block->addCrumb($crumbName, $crumb);
            $crumb += array('first' => null, 'last' => null, 'readonly' => null);
        }

        $cacheKeyInfo = $this->_block->getCacheKeyInfo();
        $crumbsFromCacheKey = unserialize(base64_decode($cacheKeyInfo['crumbs']));
        $this->assertEquals($crumbs, $crumbsFromCacheKey);
    }
}
