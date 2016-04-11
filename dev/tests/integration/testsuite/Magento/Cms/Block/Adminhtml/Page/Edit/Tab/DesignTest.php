<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Cms\Block\Adminhtml\Page\Edit\Tab;

/**
 * Test class for \Magento\Cms\Block\Adminhtml\Page\Edit\Tab\Design
 * @magentoAppArea adminhtml
 */
class DesignTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @magentoAppIsolation enabled
     */
    public function testPrepareForm()
    {
        /** @var $objectManager \Magento\TestFramework\ObjectManager */
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\View\DesignInterface'
        )->setArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->setDefaultDesignTheme();
        $objectManager->get(
            'Magento\Framework\Config\ScopeInterface'
        )->setCurrentScope(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        );
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'cms_page',
            $objectManager->create('Magento\Cms\Model\Page')
        );

        $block = $objectManager->create('Magento\Cms\Block\Adminhtml\Page\Edit\Tab\Design');
        $prepareFormMethod = new \ReflectionMethod('Magento\Cms\Block\Adminhtml\Page\Edit\Tab\Design', '_prepareForm');
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (['custom_theme_to', 'custom_theme_from'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }
    }
}
