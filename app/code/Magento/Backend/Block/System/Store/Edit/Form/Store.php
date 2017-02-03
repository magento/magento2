<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for store
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Store extends \Magento\Backend\Block\System\Store\Edit\AbstractForm
{
    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @var \Magento\Store\Model\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\GroupFactory $groupFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\GroupFactory $groupFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_groupFactory = $groupFactory;
        $this->_websiteFactory = $websiteFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare store specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareStoreFieldset(\Magento\Framework\Data\Form $form)
    {
        $storeModel = $this->_coreRegistry->registry('store_data');
        $postData = $this->_coreRegistry->registry('store_post_data');
        if ($postData) {
            $storeModel->setData($postData['store']);
        }
        $fieldset = $form->addFieldset('store_fieldset', ['legend' => __('Store View Information')]);

        $storeAction = $this->_coreRegistry->registry('store_action');
        if ($storeAction == 'edit' || $storeAction == 'add') {
            $fieldset->addField(
                'store_group_id',
                'select',
                [
                    'name' => 'store[group_id]',
                    'label' => __('Store'),
                    'value' => $storeModel->getGroupId(),
                    'values' => $this->_getStoreGroups(),
                    'required' => true,
                    'disabled' => $storeModel->isReadOnly()
                ]
            );
            if ($storeModel->getId() && $storeModel->getGroup()->getDefaultStoreId() == $storeModel->getId()) {
                if ($storeModel->getGroup() && $storeModel->getGroup()->getStoresCount() > 1) {
                    $form->getElement('store_group_id')->setDisabled(true);

                    $fieldset->addField(
                        'store_hidden_group_id',
                        'hidden',
                        ['name' => 'store[group_id]', 'no_span' => true, 'value' => $storeModel->getGroupId()]
                    );
                } else {
                    $fieldset->addField(
                        'store_original_group_id',
                        'hidden',
                        [
                            'name' => 'store[original_group_id]',
                            'no_span' => true,
                            'value' => $storeModel->getGroupId()
                        ]
                    );
                }
            }
        }

        $fieldset->addField(
            'store_name',
            'text',
            [
                'name' => 'store[name]',
                'label' => __('Name'),
                'value' => $storeModel->getName(),
                'required' => true,
                'disabled' => $storeModel->isReadOnly()
            ]
        );
        $fieldset->addField(
            'store_code',
            'text',
            [
                'name' => 'store[code]',
                'label' => __('Code'),
                'value' => $storeModel->getCode(),
                'required' => true,
                'disabled' => $storeModel->isReadOnly()
            ]
        );

        $fieldset->addField(
            'store_is_active',
            'select',
            [
                'name' => 'store[is_active]',
                'label' => __('Status'),
                'value' => $storeModel->isActive(),
                'options' => [0 => __('Disabled'), 1 => __('Enabled')],
                'required' => true,
                'disabled' => $storeModel->isReadOnly()
            ]
        );

        $fieldset->addField(
            'store_sort_order',
            'text',
            [
                'name' => 'store[sort_order]',
                'label' => __('Sort Order'),
                'value' => $storeModel->getSortOrder(),
                'required' => false,
                'disabled' => $storeModel->isReadOnly()
            ]
        );

        $fieldset->addField(
            'store_is_default',
            'hidden',
            ['name' => 'store[is_default]', 'no_span' => true, 'value' => $storeModel->getIsDefault()]
        );

        $fieldset->addField(
            'store_store_id',
            'hidden',
            [
                'name' => 'store[store_id]',
                'no_span' => true,
                'value' => $storeModel->getId(),
                'disabled' => $storeModel->isReadOnly()
            ]
        );
    }

    /**
     * Retrieve list of store groups
     *
     * @return array
     */
    protected function _getStoreGroups()
    {
        $websites = $this->_websiteFactory->create()->getCollection();
        $allgroups = $this->_groupFactory->create()->getCollection();
        $groups = [];
        foreach ($websites as $website) {
            $values = [];
            foreach ($allgroups as $group) {
                if ($group->getWebsiteId() == $website->getId()) {
                    $values[] = ['label' => $group->getName(), 'value' => $group->getId()];
                }
            }
            $groups[] = ['label' => $website->getName(), 'value' => $values];
        }
        return $groups;
    }
}
