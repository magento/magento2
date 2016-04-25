<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Block\System\Store\Edit\Form;

/**
 * Adminhtml store edit form for group
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 *
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class Group extends \Magento\Backend\Block\System\Store\Edit\AbstractForm
{
    /**
     * @var \Magento\Catalog\Model\Config\Source\Category
     */
    protected $_category;

    /**
     * @var \Magento\Store\Model\StoreFactory
     */
    protected $_storeFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Catalog\Model\Config\Source\Category $category
     * @param \Magento\Store\Model\StoreFactory $storeFactory
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Catalog\Model\Config\Source\Category $category,
        \Magento\Store\Model\StoreFactory $storeFactory,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        array $data = []
    ) {
        $this->_category = $category;
        $this->_storeFactory = $storeFactory;
        $this->_websiteFactory = $websiteFactory;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Prepare group specific fieldset
     *
     * @param \Magento\Framework\Data\Form $form
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareStoreFieldset(\Magento\Framework\Data\Form $form)
    {
        $groupModel = $this->_coreRegistry->registry('store_data');
        $postData = $this->_coreRegistry->registry('store_post_data');
        if ($postData) {
            $groupModel->setData($postData['group']);
        }

        $fieldset = $form->addFieldset('group_fieldset', ['legend' => __('Store Information')]);

        $storeAction = $this->_coreRegistry->registry('store_action');
        if ($storeAction == 'edit' || $storeAction == 'add') {
            $websites = $this->_websiteFactory->create()->getCollection()->toOptionArray();
            $fieldset->addField(
                'group_website_id',
                'select',
                [
                    'name' => 'group[website_id]',
                    'label' => __('Web Site'),
                    'value' => $groupModel->getWebsiteId(),
                    'values' => $websites,
                    'required' => true,
                    'disabled' => $groupModel->isReadOnly()
                ]
            );

            if ($groupModel->getId() && $groupModel->getWebsite()->getDefaultGroupId() == $groupModel->getId()) {
                if ($groupModel->getWebsite()->getIsDefault() || $groupModel->getWebsite()->getGroupsCount() == 1) {
                    $form->getElement('group_website_id')->setDisabled(true);

                    $fieldset->addField(
                        'group_hidden_website_id',
                        'hidden',
                        ['name' => 'group[website_id]', 'no_span' => true, 'value' => $groupModel->getWebsiteId()]
                    );
                } else {
                    $fieldset->addField(
                        'group_original_website_id',
                        'hidden',
                        [
                            'name' => 'group[original_website_id]',
                            'no_span' => true,
                            'value' => $groupModel->getWebsiteId()
                        ]
                    );
                }
            }
        }

        $fieldset->addField(
            'group_name',
            'text',
            [
                'name' => 'group[name]',
                'label' => __('Name'),
                'value' => $groupModel->getName(),
                'required' => true,
                'disabled' => $groupModel->isReadOnly()
            ]
        );

        $categories = $this->_category->toOptionArray();

        $fieldset->addField(
            'group_root_category_id',
            'select',
            [
                'name' => 'group[root_category_id]',
                'label' => __('Root Category'),
                'value' => $groupModel->getRootCategoryId(),
                'values' => $categories,
                'required' => true,
                'disabled' => $groupModel->isReadOnly()
            ]
        );
        if ($this->_coreRegistry->registry('store_action') == 'edit') {
            $storeActive = 1;
            $stores = $this->_storeFactory->create()->getCollection()
                ->addGroupFilter($groupModel->getId())
                ->addStatusFilter($storeActive)
                ->toOptionArray();
            $fieldset->addField(
                'group_default_store_id',
                'select',
                [
                    'name' => 'group[default_store_id]',
                    'label' => __('Default Store View'),
                    'value' => $groupModel->getDefaultStoreId(),
                    'values' => $stores,
                    'required' => false,
                    'disabled' => $groupModel->isReadOnly()
                ]
            );
        }

        $fieldset->addField(
            'group_group_id',
            'hidden',
            ['name' => 'group[group_id]', 'no_span' => true, 'value' => $groupModel->getId()]
        );
    }
}
