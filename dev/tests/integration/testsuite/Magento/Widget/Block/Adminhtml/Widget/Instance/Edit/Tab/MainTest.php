<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab;

/**
 * @magentoAppArea adminhtml
 */
class MainTest extends \PHPUnit_Framework_TestCase
{
    public function testPackageThemeElement()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(\Magento\Framework\Registry::class)
            ->register('current_widget_instance', new \Magento\Framework\DataObject());
        /** @var \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main $block */
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\LayoutInterface::class
        )->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main::class
        );
        $block->setTemplate(null);
        $block->toHtml();
        $element = $block->getForm()->getElement('theme_id');
        $this->assertInstanceOf(\Magento\Framework\Data\Form\Element\Select::class, $element);
        $this->assertTrue($element->getDisabled());
    }

    public function testTypeElement()
    {
        $block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\View\Layout::class
        )->createBlock(
            \Magento\Widget\Block\Adminhtml\Widget\Instance\Edit\Tab\Main::class
        );
        $block->setTemplate(null);
        $block->toHtml();
        $element = $block->getForm()->getElement('instance_code');
        $this->assertInstanceOf(\Magento\Framework\Data\Form\Element\Select::class, $element);
        $this->assertTrue($element->getDisabled());
    }
}
