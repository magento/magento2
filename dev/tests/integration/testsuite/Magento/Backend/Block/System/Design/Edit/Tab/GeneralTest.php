<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Design\Edit\Tab;

/**
 * Test class for \Magento\Backend\Block\System\Design\Edit\Tab\General
 * @magentoAppArea adminhtml
 */
class GeneralTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            \Magento\Framework\View\DesignInterface::class
        )->setArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->setDefaultDesignTheme();
        $objectManager->get(
            \Magento\Framework\Registry::class
        )->register(
            'design',
            $objectManager->create(\Magento\Framework\App\DesignInterface::class)
        );
        $layout = $objectManager->create(\Magento\Framework\View\Layout::class);
        $block = $layout->addBlock(\Magento\Backend\Block\System\Design\Edit\Tab\General::class);
        $prepareFormMethod = new \ReflectionMethod(
            \Magento\Backend\Block\System\Design\Edit\Tab\General::class,
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (['date_from', 'date_to'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
