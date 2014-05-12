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
        array $data = array()
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

        $fieldset = $form->addFieldset('group_fieldset', array('legend' => __('Store Information')));

        $storeAction = $this->_coreRegistry->registry('store_action');
        if ($storeAction == 'edit' || $storeAction == 'add') {
            $websites = $this->_websiteFactory->create()->getCollection()->toOptionArray();
            $fieldset->addField(
                'group_website_id',
                'select',
                array(
                    'name' => 'group[website_id]',
                    'label' => __('Web Site'),
                    'value' => $groupModel->getWebsiteId(),
                    'values' => $websites,
                    'required' => true,
                    'disabled' => $groupModel->isReadOnly()
                )
            );

            if ($groupModel->getId() && $groupModel->getWebsite()->getDefaultGroupId() == $groupModel->getId()) {
                if ($groupModel->getWebsite()->getIsDefault() || $groupModel->getWebsite()->getGroupsCount() == 1) {
                    $form->getElement('group_website_id')->setDisabled(true);

                    $fieldset->addField(
                        'group_hidden_website_id',
                        'hidden',
                        array('name' => 'group[website_id]', 'no_span' => true, 'value' => $groupModel->getWebsiteId())
                    );
                } else {
                    $fieldset->addField(
                        'group_original_website_id',
                        'hidden',
                        array(
                            'name' => 'group[original_website_id]',
                            'no_span' => true,
                            'value' => $groupModel->getWebsiteId()
                        )
                    );
                }
            }
        }

        $fieldset->addField(
            'group_name',
            'text',
            array(
                'name' => 'group[name]',
                'label' => __('Name'),
                'value' => $groupModel->getName(),
                'required' => true,
                'disabled' => $groupModel->isReadOnly()
            )
        );

        $categories = $this->_category->toOptionArray();

        $fieldset->addField(
            'group_root_category_id',
            'select',
            array(
                'name' => 'group[root_category_id]',
                'label' => __('Root Category'),
                'value' => $groupModel->getRootCategoryId(),
                'values' => $categories,
                'required' => true,
                'disabled' => $groupModel->isReadOnly()
            )
        );

        if ($this->_coreRegistry->registry('store_action') == 'edit') {
            $stores = $this->_storeFactory->create()->getCollection()->addGroupFilter(
                $groupModel->getId()
            )->toOptionArray();
            $fieldset->addField(
                'group_default_store_id',
                'select',
                array(
                    'name' => 'group[default_store_id]',
                    'label' => __('Default Store View'),
                    'value' => $groupModel->getDefaultStoreId(),
                    'values' => $stores,
                    'required' => false,
                    'disabled' => $groupModel->isReadOnly()
                )
            );
        }

        $fieldset->addField(
            'group_group_id',
            'hidden',
            array('name' => 'group[group_id]', 'no_span' => true, 'value' => $groupModel->getId())
        );
    }
}
