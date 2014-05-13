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
        foreach (array('customer_group_ids', 'from_date', 'to_date') as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $actual = ($id == 'customer_group_ids') ? $element->getValues() : $element->getDateFormat();
            $this->assertNotEmpty($actual);
        }
    }
}
