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

/**
 * Catalog Rule General Information Tab
 *
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Block\Adminhtml\Promo\Catalog\Edit\Tab;

use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Backend\Block\Widget\Tab\TabInterface;

class Main extends Generic implements TabInterface
{
    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Service\V1\CustomerGroupServiceInterface
     */
    protected $_customerGroup;

    /**
     * @var \Magento\Framework\Convert\Object
     */
    protected $_objectConverter;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroup
     * @param \Magento\Framework\Convert\Object $objectConverter
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Customer\Service\V1\CustomerGroupServiceInterface $customerGroup,
        \Magento\Framework\Convert\Object $objectConverter,
        \Magento\Store\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_customerGroup = $customerGroup;
        $this->_objectConverter = $objectConverter;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare content for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Rule Information');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Rule Information');
    }

    /**
     * Returns status flag about this tab can be showed or not
     *
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * @return Form
     */
    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_promo_catalog_rule');

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset', array('legend' => __('General Information')));

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array('name' => 'rule_id'));
        }

        $fieldset->addField(
            'name',
            'text',
            array('name' => 'name', 'label' => __('Rule Name'), 'title' => __('Rule Name'), 'required' => true)
        );

        $fieldset->addField(
            'description',
            'textarea',
            array(
                'name' => 'description',
                'label' => __('Description'),
                'title' => __('Description'),
                'style' => 'height: 100px;'
            )
        );

        $fieldset->addField(
            'is_active',
            'select',
            array(
                'label' => __('Status'),
                'title' => __('Status'),
                'name' => 'is_active',
                'required' => true,
                'options' => array('1' => __('Active'), '0' => __('Inactive'))
            )
        );

        if ($this->_storeManager->isSingleStoreMode()) {
            $websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', array('name' => 'website_ids[]', 'value' => $websiteId));
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField(
                'website_ids',
                'multiselect',
                array(
                    'name' => 'website_ids[]',
                    'label' => __('Websites'),
                    'title' => __('Websites'),
                    'required' => true,
                    'values' => $this->_systemStore->getWebsiteValuesForForm()
                )
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        }

        $fieldset->addField(
            'customer_group_ids',
            'multiselect',
            array(
                'name' => 'customer_group_ids[]',
                'label' => __('Customer Groups'),
                'title' => __('Customer Groups'),
                'required' => true,
                'values' => $this->_objectConverter->toOptionArray($this->_customerGroup->getGroups(), 'id', 'code')
            )
        );

        $dateFormat = $this->_localeDate->getDateFormat(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField(
            'from_date',
            'date',
            array(
                'name' => 'from_date',
                'label' => __('From Date'),
                'title' => __('From Date'),
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            )
        );
        $fieldset->addField(
            'to_date',
            'date',
            array(
                'name' => 'to_date',
                'label' => __('To Date'),
                'title' => __('To Date'),
                'image' => $this->getViewFileUrl('images/grid-cal.gif'),
                'input_format' => \Magento\Framework\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
                'date_format' => $dateFormat
            )
        );

        $fieldset->addField('sort_order', 'text', array('name' => 'sort_order', 'label' => __('Priority')));

        $form->setValues($model->getData());

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        $this->setForm($form);

        $this->_eventManager->dispatch('adminhtml_promo_catalog_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}
