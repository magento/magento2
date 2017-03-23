<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance;

/**
 * @magentoAppArea adminhtml
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     */
    public function testConstruct()
    {
        $type = \Magento\Catalog\Block\Product\Widget\NewWidget::class;
        $code = 'catalog_product_newwidget';
        $theme = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\DesignInterface::class
        )->setDefaultDesignTheme()->getDesignTheme();

        /** @var $widgetInstance \Magento\Widget\Model\Widget\Instance */
        $widgetInstance = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Widget\Model\Widget\Instance::class
        );
        $widgetInstance->setType($type)->setCode($code)->setThemeId($theme->getId())->save();
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)->register('current_widget_instance', $widgetInstance);

        \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\App\RequestInterface::class
        )->setParam(
            'instance_id',
            $widgetInstance->getId()
        );
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit::class,
            'widget'
        );
        $this->assertArrayHasKey('widget-delete_button', $block->getLayout()->getAllBlocks());
    }
}
