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

namespace Magento\Customer\Block\Adminhtml\Group\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Customer\Service\V1\Data\CustomerGroup;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Magento\Customer\Block\Adminhtml\Group\Edit\Form
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupService
     */
    private $customerGroupService;

    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;

    /**
     * Execute per test initialization.
     */
    public function setUp()
    {
        parent::setUp();
        $this->layout = Bootstrap::getObjectManager()->create(
            'Magento\Framework\View\Layout'
        );
        $this->customerGroupService = Bootstrap::getObjectManager()
            ->get('Magento\Customer\Service\V1\CustomerGroupServiceInterface');
        $this->registry = Bootstrap::getObjectManager()->get('Magento\Framework\Registry');
    }

    /**
     * Execute per test cleanup.
     */
    public function tearDown()
    {
        $this->registry->unregister(RegistryConstants::CURRENT_GROUP_ID);
    }

    /**
     * Test retrieving a valid group form.
     */
    public function testGetForm()
    {
        $this->registry
            ->register(RegistryConstants::CURRENT_GROUP_ID, $this->customerGroupService->getDefaultGroup(0)->getId());

        /** @var $block Form */
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Group\Edit\Form', 'block');
        $form = $block->getForm();

        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());
        $baseFieldSet = $form->getElement('base_fieldset');
        $this->assertNotNull($baseFieldSet);
        $groupCodeElement = $form->getElement('customer_group_code');
        $this->assertNotNull($groupCodeElement);
        $taxClassIdElement = $form->getElement('tax_class_id');
        $this->assertNotNull($taxClassIdElement);
        $idElement = $form->getElement('id');
        $this->assertNotNull($idElement);
        $this->assertEquals('1', $idElement->getValue());
        $this->assertEquals('3', $taxClassIdElement->getValue());
        /** @var \Magento\Tax\Model\TaxClass\Source\Customer $taxClassCustomer */
        $taxClassCustomer = Bootstrap::getObjectManager()->get('Magento\Tax\Model\TaxClass\Source\Customer');
        $this->assertEquals($taxClassCustomer->toOptionArray(false), $taxClassIdElement->getData('values'));
        $this->assertEquals('General', $groupCodeElement->getValue());
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testGetFormExistInCustomGroup()
    {
        $builder = Bootstrap::getObjectManager()->create('Magento\Framework\Service\V1\Data\FilterBuilder');
        /** @var \Magento\Framework\Service\V1\Data\SearchCriteriaBuilder $searchCriteria */
        $searchCriteria = Bootstrap::getObjectManager()
            ->create('Magento\Framework\Service\V1\Data\SearchCriteriaBuilder')
            ->addFilter([$builder->setField('code')->setValue('custom_group')->create()])->create();
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->customerGroupService->searchGroups($searchCriteria)->getItems()[0];
        $this->registry->register(RegistryConstants::CURRENT_GROUP_ID, $customerGroup->getId());

        /** @var $block Form */
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Group\Edit\Form', 'block');
        $form = $block->getForm();

        $this->assertEquals('edit_form', $form->getId());
        $this->assertEquals('post', $form->getMethod());
        $baseFieldSet = $form->getElement('base_fieldset');
        $this->assertNotNull($baseFieldSet);
        $groupCodeElement = $form->getElement('customer_group_code');
        $this->assertNotNull($groupCodeElement);
        $taxClassIdElement = $form->getElement('tax_class_id');
        $this->assertNotNull($taxClassIdElement);
        $idElement = $form->getElement('id');
        $this->assertNotNull($idElement);
        $this->assertEquals($customerGroup->getId(), $idElement->getValue());
        $this->assertEquals($customerGroup->getTaxClassId(), $taxClassIdElement->getValue());
        /** @var \Magento\Tax\Model\TaxClass\Source\Customer $taxClassCustomer */
        $taxClassCustomer = Bootstrap::getObjectManager()->get('Magento\Tax\Model\TaxClass\Source\Customer');
        $this->assertEquals($taxClassCustomer->toOptionArray(false), $taxClassIdElement->getData('values'));
        $this->assertEquals($customerGroup->getCode(), $groupCodeElement->getValue());
    }
}
