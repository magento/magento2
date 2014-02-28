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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping Cart Price Rule General Information Tab
 *
 * @category Magento
 * @package Magento_Adminhtml
 * @author Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab;

class Main
    extends \Magento\Backend\Block\Widget\Form\Generic
    implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var \Magento\Core\Model\System\Store
     */
    protected $_systemStore;

    /**
     * @var \Magento\Customer\Model\Resource\Group\CollectionFactory
     */
    protected $_customerGroup;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $_salesRule;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Registry $registry
     * @param \Magento\Data\FormFactory $formFactory
     * @param \Magento\SalesRule\Model\RuleFactory $salesRule
     * @param \Magento\Customer\Model\Resource\Group\CollectionFactory $customerGroup
     * @param \Magento\Core\Model\System\Store $systemStore
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Registry $registry,
        \Magento\Data\FormFactory $formFactory,
        \Magento\SalesRule\Model\RuleFactory $salesRule,
        \Magento\Customer\Model\Resource\Group\CollectionFactory $customerGroup,
        \Magento\Core\Model\System\Store $systemStore,
        array $data = array()
    ) {
        $this->_systemStore = $systemStore;
        $this->_customerGroup = $customerGroup;
        $this->_salesRule = $salesRule;
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
     * @return true
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * Returns status flag about this tab hidden or not
     *
     * @return true
     */
    public function isHidden()
    {
        return false;
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('current_promo_quote_rule');

        /** @var \Magento\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('rule_');

        $fieldset = $form->addFieldset('base_fieldset',
            array('legend' => __('General Information'))
        );

        if ($model->getId()) {
            $fieldset->addField('rule_id', 'hidden', array(
                'name' => 'rule_id',
            ));
        }

        $fieldset->addField('product_ids', 'hidden', array(
            'name' => 'product_ids',
        ));

        $fieldset->addField('name', 'text', array(
            'name' => 'name',
            'label' => __('Rule Name'),
            'title' => __('Rule Name'),
            'required' => true,
        ));

        $fieldset->addField('description', 'textarea', array(
            'name' => 'description',
            'label' => __('Description'),
            'title' => __('Description'),
            'style' => 'height: 100px;',
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'     => __('Status'),
            'title'     => __('Status'),
            'name'      => 'is_active',
            'required' => true,
            'options'    => array(
                '1' => __('Active'),
                '0' => __('Inactive'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        if ($this->_storeManager->isSingleStoreMode()) {
            $websiteId = $this->_storeManager->getStore(true)->getWebsiteId();
            $fieldset->addField('website_ids', 'hidden', array(
                'name'     => 'website_ids[]',
                'value'    => $websiteId
            ));
            $model->setWebsiteIds($websiteId);
        } else {
            $field = $fieldset->addField('website_ids', 'multiselect', array(
                'name'     => 'website_ids[]',
                'label'     => __('Websites'),
                'title'     => __('Websites'),
                'required' => true,
                'values'   => $this->_systemStore->getWebsiteValuesForForm(),
            ));
            $renderer = $this->getLayout()->createBlock('Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element');
            $field->setRenderer($renderer);
        }

        $customerGroups = $this->_customerGroup->create()->load()->toOptionArray();
        $found = false;

        foreach ($customerGroups as $group) {
            if ($group['value'] == 0) {
                $found = true;
            }
        }
        if (!$found) {
            array_unshift($customerGroups, array(
                'value' => 0,
                'label' => __('NOT LOGGED IN'))
            );
        }

        $fieldset->addField('customer_group_ids', 'multiselect', array(
            'name'      => 'customer_group_ids[]',
            'label'     => __('Customer Groups'),
            'title'     => __('Customer Groups'),
            'required'  => true,
            'values'    => $this->_customerGroup->create()->toOptionArray(),
        ));

        $couponTypeFiled = $fieldset->addField('coupon_type', 'select', array(
            'name'       => 'coupon_type',
            'label'      => __('Coupon'),
            'required'   => true,
            'options'    => $this->_salesRule->create()->getCouponTypes(),
        ));

        $couponCodeFiled = $fieldset->addField('coupon_code', 'text', array(
            'name' => 'coupon_code',
            'label' => __('Coupon Code'),
            'required' => true,
        ));

        $autoGenerationCheckbox = $fieldset->addField('use_auto_generation', 'checkbox', array(
            'name'  => 'use_auto_generation',
            'label' => __('Use Auto Generation'),
            'note'  => __('If you select and save the rule you will be able to generate multiple coupon codes.'),
            'onclick' => 'handleCouponsTabContentActivity()',
            'checked' => (int)$model->getUseAutoGeneration() > 0 ? 'checked' : ''
        ));

        $autoGenerationCheckbox->setRenderer(
            $this->getLayout()->createBlock('Magento\SalesRule\Block\Adminhtml\Promo\Quote\Edit\Tab\Main\Renderer\Checkbox')
        );

        $usesPerCouponFiled = $fieldset->addField('uses_per_coupon', 'text', array(
            'name' => 'uses_per_coupon',
            'label' => __('Uses per Coupon'),
        ));

        $fieldset->addField('uses_per_customer', 'text', array(
            'name' => 'uses_per_customer',
            'label' => __('Uses per Customer'),
        ));

        $dateFormat = $this->_locale->getDateFormat(\Magento\Core\Model\LocaleInterface::FORMAT_TYPE_SHORT);
        $fieldset->addField('from_date', 'date', array(
            'name'   => 'from_date',
            'label'  => __('From Date'),
            'title'  => __('From Date'),
            'image'  => $this->getViewFileUrl('images/grid-cal.gif'),
            'input_format' => \Magento\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ));
        $fieldset->addField('to_date', 'date', array(
            'name'   => 'to_date',
            'label'  => __('To Date'),
            'title'  => __('To Date'),
            'image'  => $this->getViewFileUrl('images/grid-cal.gif'),
            'input_format' => \Magento\Stdlib\DateTime::DATE_INTERNAL_FORMAT,
            'date_format'  => $dateFormat
        ));

        $fieldset->addField('sort_order', 'text', array(
            'name' => 'sort_order',
            'label' => __('Priority'),
        ));

        $fieldset->addField('is_rss', 'select', array(
            'label'     => __('Public In RSS Feed'),
            'title'     => __('Public In RSS Feed'),
            'name'      => 'is_rss',
            'options'   => array(
                '1' => __('Yes'),
                '0' => __('No'),
            ),
        ));

        if(!$model->getId()){
            //set the default value for is_rss feed to yes for new promotion
            $model->setIsRss(1);
        }

        $form->setValues($model->getData());

        $autoGenerationCheckbox->setValue(1);

        if ($model->isReadonly()) {
            foreach ($fieldset->getElements() as $element) {
                $element->setReadonly(true, true);
            }
        }

        //$form->setUseContainer(true);

        $this->setForm($form);

        // field dependencies
        $this->setChild('form_after', $this->getLayout()
            ->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
            ->addFieldMap($couponTypeFiled->getHtmlId(), $couponTypeFiled->getName())
            ->addFieldMap($couponCodeFiled->getHtmlId(), $couponCodeFiled->getName())
            ->addFieldMap($autoGenerationCheckbox->getHtmlId(), $autoGenerationCheckbox->getName())
            ->addFieldMap($usesPerCouponFiled->getHtmlId(), $usesPerCouponFiled->getName())
            ->addFieldDependence(
                $couponCodeFiled->getName(),
                $couponTypeFiled->getName(),
                \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $autoGenerationCheckbox->getName(),
                $couponTypeFiled->getName(),
                \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
            ->addFieldDependence(
                $usesPerCouponFiled->getName(),
                $couponTypeFiled->getName(),
                \Magento\SalesRule\Model\Rule::COUPON_TYPE_SPECIFIC)
        );

        $this->_eventManager->dispatch('adminhtml_promo_quote_edit_tab_main_prepare_form', array('form' => $form));

        return parent::_prepareForm();
    }
}
