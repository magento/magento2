<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for website
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Website extends \Magento\Backend\Block\System\Store\Edit\AbstractForm
{
    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare website specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     */
    protected function _prepareStoreFieldset(\Magento\Framework\Data\Form $form)
    {
        $websiteModel = $this->_coreRegistry->registry('store_data');
        $postData = $this->_coreRegistry->registry('store_post_data');
        if ($postData) {
            $websiteModel->setData($postData['website']);
        }
        $fieldset = $form->addFieldset('website_fieldset', ['legend' => __('Web Site Information')]);
        /* @var $fieldset \Magento\Framework\Data\Form */

        $fieldset->addField(
            'website_name',
            'text',
            [
                'name' => 'website[name]',
                'label' => __('Name'),
                'value' => $websiteModel->getName(),
                'required' => true,
                'disabled' => $websiteModel->isReadOnly()
            ]
        );

        $fieldset->addField(
            'website_code',
            'text',
            [
                'name' => 'website[code]',
                'label' => __('Code'),
                'value' => $websiteModel->getCode(),
                'required' => true,
                'disabled' => $websiteModel->isReadOnly()
            ]
        );

        $fieldset->addField(
            'website_sort_order',
            'text',
            [
                'name' => 'website[sort_order]',
                'label' => __('Sort Order'),
                'value' => $websiteModel->getSortOrder(),
                'required' => false,
                'disabled' => $websiteModel->isReadOnly()
            ]
        );

        if ($this->_coreRegistry->registry('store_action') == 'edit') {
            $groups = $this->_groupFactory->create()->getCollection()->addWebsiteFilter(
                $websiteModel->getId()
            )->setWithoutStoreViewFilter()->toOptionArray();

            $fieldset->addField(
                'website_default_group_id',
                'select',
                [
                    'name' => 'website[default_group_id]',
                    'label' => __('Default Store'),
                    'value' => $websiteModel->getDefaultGroupId(),
                    'values' => $groups,
                    'required' => false,
                    'disabled' => $websiteModel->isReadOnly()
                ]
            );
        }

        if ($this->checkIsSingleAndIsDefaultStore($websiteModel)) {
            $fieldset->addField(
                'is_default',
                'checkbox',
                [
                    'name' => 'website[is_default]',
                    'label' => __('Set as Default'),
                    'value' => 1,
                    'disabled' => $websiteModel->isReadOnly()
                ]
            );
        } else {
            $fieldset->addField(
                'is_default',
                'hidden',
                ['name' => 'website[is_default]', 'value' => $websiteModel->getIsDefault()]
            );
        }

        $fieldset->addField(
            'website_website_id',
            'hidden',
            ['name' => 'website[website_id]', 'value' => $websiteModel->getId()]
        );
    }

    private function checkIsSingleAndIsDefaultStore($websiteModel)
    {
        $hasOnlyDefaultStore = $websiteModel->getStoresCount() == 1 &&
            isset($websiteModel->getStoreIds()[\Magento\Store\Model\Store::DEFAULT_STORE_ID]);

        return !$websiteModel->getIsDefault() && $websiteModel->getStoresCount() && !$hasOnlyDefaultStore;
    }
}
