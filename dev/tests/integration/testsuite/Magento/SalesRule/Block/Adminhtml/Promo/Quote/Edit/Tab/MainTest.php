<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

/**
 * Test class for \Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main
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
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $objectManager->get(
            'Magento\Framework\View\DesignInterface'
        )->setArea(
            \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE
        )->setDefaultDesignTheme();
        $objectManager->get(
            'Magento\Framework\Registry'
        )->register(
            'current_promo_quote_rule',
            $objectManager->create('Magento\SalesRule\Model\Rule')
        );

        $layout = $objectManager->create('Magento\Framework\View\Layout');
        $block = $layout->createBlock('Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main');
        $prepareFormMethod = new \ReflectionMethod(
            'Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main',
            '_prepareForm'
        );
        $prepareFormMethod->setAccessible(true);
        $prepareFormMethod->invoke($block);

        $form = $block->getForm();
        foreach (['from_date', 'to_date'] as $id) {
            $element = $form->getElement($id);
            $this->assertNotNull($element);
            $this->assertNotEmpty($element->getDateFormat());
        }

        // assert Customer Groups field
        $customerGroupsField = $form->getElement('customer_group_ids');
        /** @var \Magento\Customer\Api\GroupRepositoryInterface $groupRepository */
        $groupRepository = $objectManager->create('Magento\Customer\Api\GroupRepositoryInterface');
        /** @var \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = $objectManager->create('Magento\Framework\Api\SearchCriteriaBuilder');
        $objectConverter = $objectManager->get('Magento\Framework\Convert\DataObject');
        $groups = $groups = $groupRepository->getList($searchCriteria->create())
            ->getItems();
        $expected = $objectConverter->toOptionArray($groups, 'id', 'code');
        $this->assertEquals($expected, $customerGroupsField->getValues());
    }
}
