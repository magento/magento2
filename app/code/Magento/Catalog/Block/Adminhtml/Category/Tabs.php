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
 * Category tabs
 */
namespace Magento\Catalog\Block\Adminhtml\Category;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Default Attribute Tab Block
     *
     * @var string
     */
    protected $_attributeTabBlock = 'Magento\Catalog\Block\Adminhtml\Category\Tab\Attributes';

    /**
     * @var string
     */
    protected $_template = 'Magento_Backend::widget/tabshoriz.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Catalog helper
     *
     * @var \Magento\Catalog\Helper\Catalog
     */
    protected $_helperCatalog = null;

    /**
     * @var \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Catalog $helperCatalog
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Eav\Model\Resource\Entity\Attribute\Group\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Catalog $helperCatalog,
        \Magento\Framework\Registry $registry,
        array $data = array()
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_helperCatalog = $helperCatalog;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Initialize Tabs
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('category_info_tabs');
        $this->setDestElementId('category_tab_content');
        $this->setTitle(__('Category Data'));
    }

    /**
     * Retrieve category object
     *
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        return $this->_coreRegistry->registry('current_category');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if ($block = $this->_helperCatalog->getCategoryAttributeTabBlock()) {
            return $block;
        }
        return $this->_attributeTabBlock;
    }

    /**
     * Prepare Layout Content
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $categoryAttributes = $this->getCategory()->getAttributes();
        if (!$this->getCategory()->getId()) {
            foreach ($categoryAttributes as $attribute) {
                $default = $attribute->getDefaultValue();
                if ($default != '') {
                    $this->getCategory()->setData($attribute->getAttributeCode(), $default);
                }
            }
        }

        $attributeSetId = $this->getCategory()->getDefaultAttributeSetId();
        /** @var $groupCollection \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection */
        $groupCollection = $this->_collectionFactory->create()->setAttributeSetFilter(
            $attributeSetId
        )->setSortOrder()->load();
        $defaultGroupId = 0;
        foreach ($groupCollection as $group) {
            /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
            if ($defaultGroupId == 0 or $group->getIsDefault()) {
                $defaultGroupId = $group->getId();
            }
        }

        foreach ($groupCollection as $group) {
            /* @var $group \Magento\Eav\Model\Entity\Attribute\Group */
            $attributes = array();
            foreach ($categoryAttributes as $attribute) {
                /* @var $attribute \Magento\Eav\Model\Entity\Attribute */
                if ($attribute->isInGroup($attributeSetId, $group->getId())) {
                    $attributes[] = $attribute;
                }
            }

            // do not add grops without attributes
            if (!$attributes) {
                continue;
            }

            $active = $defaultGroupId == $group->getId();
            $block = $this->getLayout()->createBlock(
                $this->getAttributeTabBlock(),
                $this->getNameInLayout() . '_tab_' . $group->getAttributeGroupName()
            )->setGroup(
                $group
            )->setAttributes(
                $attributes
            )->setAddHiddenFields(
                $active
            )->toHtml();
            $this->addTab(
                'group_' . $group->getId(),
                array('label' => __($group->getAttributeGroupName()), 'content' => $block, 'active' => $active)
            );
        }

        $this->addTab(
            'products',
            array(
                'label' => __('Category Products'),
                'content' => $this->getLayout()->createBlock(
                    'Magento\Catalog\Block\Adminhtml\Category\Tab\Product',
                    'category.product.grid'
                )->toHtml()
            )
        );

        // dispatch event add custom tabs
        $this->_eventManager->dispatch('adminhtml_catalog_category_tabs', array('tabs' => $this));

        /*$this->addTab('features', array(
          'label'     => __('Feature Products'),
          'content'   => 'Feature Products'
          ));        */
        return parent::_prepareLayout();
    }
}
