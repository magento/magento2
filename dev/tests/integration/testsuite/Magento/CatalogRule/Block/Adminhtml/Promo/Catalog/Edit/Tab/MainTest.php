<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab;

/**
 * Test class for \Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Main
 *
 * @magentoAppArea adminhtml
 */
class MainTest extends \PHPUnit_Framework_TestCase
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
        $rule = $objectManager->create('Magento\CatalogRule\Model\Rule');
        $objectManager->get('Magento\Framework\Registry')->register('current_promo_catalog_rule', $rule);

        $block = $objectManager->create('Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Main');
        $block->setLayout($objectManager->create('Magento\Framework\View\Layout'));
        $prepareFormMethod = new \ReflectionMethod(
            'Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab\Main',
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (['customer_group_ids', 'from_date', 'to_date'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $actual = ($id == 'customer_group_ids') ? $element->getValues() : $element->getDateFormat();
            $this->assertNotEmpty($actual);
        }
    }
}
