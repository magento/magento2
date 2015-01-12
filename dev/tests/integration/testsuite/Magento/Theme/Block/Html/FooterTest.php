<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Block\Html;

use Magento\Customer\Model\Context;

class FooterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Theme
     */
    protected $_theme;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\App\State')
            ->setAreaCode('frontend');
        $design = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\DesignInterface'
        );
        $this->_theme = $design->setDefaultDesignTheme()->getDesignTheme();
    }

    public function testGetCacheKeyInfo()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $context = $objectManager->get('Magento\Framework\App\Http\Context');
        $context->setValue(Context::CONTEXT_AUTH, false, false);
        $block = $objectManager->get('Magento\Framework\View\LayoutInterface')
            ->createBlock('Magento\Theme\Block\Html\Footer');
        $storeId = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
        $this->assertEquals(
            ['PAGE_FOOTER', $storeId, 0, $this->_theme->getId(), null],
            $block->getCacheKeyInfo()
        );
    }
}
