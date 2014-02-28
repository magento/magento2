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
 * @category    Magento
 * @package     Magento_Customer
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml customer groups edit form
 *
 * @category   Magento
 * @package    Magento_Customer
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Customer\Block\Adminhtml\Group\Edit;

class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * @var \Magento\Tax\Model\TaxClass\Source\Customer
     */
    protected $_taxCustomer;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\Tax\Model\TaxClass\Source\Customer $taxCustomer,
        array $data = array()
    ) {
        $this->_taxCustomer = $taxCustomer;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare form for render
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();

        /** @var \Magento\Customer\Service\V1\Dto\CustomerGroup $customerGroup */
        $customerGroup = $this->_coreRegistry->registry('current_group');

        $fieldset = $form->addFieldset('base_fieldset', array('legend'=>__('Group Information')));

        $validateClass = sprintf('required-entry validate-length maximum-length-%d',
            \Magento\Customer\Service\V1\CustomerGroupServiceInterface::GROUP_CODE_MAX_LENGTH);
        $name = $fieldset->addField('customer_group_code', 'text',
            array(
                'name'  => 'code',
                'label' => __('Group Name'),
                'title' => __('Group Name'),
                'note'  => __('Maximum length must be less then %1 symbols',
                    \Magento\Customer\Service\V1\CustomerGroupServiceInterface::GROUP_CODE_MAX_LENGTH),
                'class' => $validateClass,
                'required' => true,
            )
        );

        if ($customerGroup->getId()==0 && $customerGroup->getCode() ) {
            $name->setDisabled(true);
        }

        $fieldset->addField('tax_class_id', 'select',
            array(
                'name'  => 'tax_class',
                'label' => __('Tax Class'),
                'title' => __('Tax Class'),
                'class' => 'required-entry',
                'required' => true,
                'values' => $this->_taxCustomer->toOptionArray()
            )
        );

        if (!is_null($customerGroup->getId())) {
            // If edit add id
            $form->addField('id', 'hidden',
                array(
                    'name'  => 'id',
                    'value' => $customerGroup->getId(),
                )
            );
        }

        if ( $this->_backendSession->getCustomerGroupData() ) {
            $form->addValues($this->_backendSession->getCustomerGroupData());
            $this->_backendSession->setCustomerGroupData(null);
        } else {
            // TODO: need to figure out how the DTOs can work with forms
            $form->addValues([
                'id'                  => $customerGroup->getId(),
                'customer_group_code' => $customerGroup->getCode(),
                'tax_class_id'        => $customerGroup->getTaxClassId(),
            ]);
        }

        $form->setUseContainer(true);
        $form->setId('edit_form');
        $form->setAction($this->getUrl('customer/*/save'));
        $this->setForm($form);
    }
}
