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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Adminhtml Catalog Attribute Set Main Block
 *
 * @category    Magento
 * @package     Magento_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Adminhtml\Block\Catalog\Product\Attribute\Set;

class Main extends \Magento\Backend\Block\Template
{
    protected $_template = 'catalog/product/attribute/set/main.phtml';

    /**
     * Catalog product
     *
     * @var \Magento\Catalog\Helper\Product
     */
    protected $_catalogProduct = null;

    /**
     * Core registry
     *
     * @var \Magento\Core\Model\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Eav\Model\Entity\TypeFactory
     */
    protected $_typeFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\GroupFactory
     */
    protected $_groupFactory;

    /**
     * @var \Magento\Catalog\Model\Resource\Product\Type\Configurable\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @param \Magento\Eav\Model\Entity\TypeFactory $typeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\Resource\Product\Type\Configurable\AttributeFactory $attributeFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Catalog\Helper\Product $catalogProduct
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Core\Model\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Catalog\Model\Resource\Product\Type\Configurable\AttributeFactory $attributeFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Magento\Core\Helper\Data $coreData,
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Core\Model\Registry $registry,
        array $data = array()
    ) {
        $this->_typeFactory = $typeFactory;
        $this->_groupFactory = $groupFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->_catalogProduct = $catalogProduct;
        parent::__construct($coreData, $context, $data);
    }

    /**
     * Prepare Global Layout
     *
     * @return \Magento\Adminhtml\Block\Catalog\Product\Attribute\Set\Main
     */
    protected function _prepareLayout()
    {
        $setId = $this->_getSetId();

        $this->addChild('group_tree', 'Magento\Adminhtml\Block\Catalog\Product\Attribute\Set\Main\Tree\Group');

        $this->addChild('edit_set_form', 'Magento\Adminhtml\Block\Catalog\Product\Attribute\Set\Main\Formset');

        $this->addChild('delete_group_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Delete Selected Group'),
            'onclick'   => 'editSet.submit();',
            'class'     => 'delete'
        ));

        $this->addChild('add_group_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Add New'),
            'onclick'   => 'editSet.addGroup();',
            'class'     => 'add'
        ));

        $this->addChild('back_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Back'),
            'onclick'   => 'setLocation(\''.$this->getUrl('*/*/').'\')',
            'class'     => 'back'
        ));

        $this->addChild('reset_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Reset'),
            'onclick'   => 'window.location.reload()'
        ));

        $this->addChild('save_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Save Attribute Set'),
            'onclick'   => 'editSet.save();',
            'class'     => 'save'
        ));

        $this->addChild('delete_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('Delete Attribute Set'),
            'onclick'   => 'deleteConfirm(\''. $this->jsQuoteEscape(__('You are about to delete all products in this set. Are you sure you want to delete this attribute set?')) . '\', \'' . $this->getUrl('*/*/delete', array('id' => $setId)) . '\')',
            'class'     => 'delete'
        ));

        $this->addChild('rename_button', 'Magento\Adminhtml\Block\Widget\Button', array(
            'label'     => __('New Set Name'),
            'onclick'   => 'editSet.rename()'
        ));

        return parent::_prepareLayout();
    }

    /**
     * Retrieve Attribute Set Group Tree HTML
     *
     * @return string
     */
    public function getGroupTreeHtml()
    {
        return $this->getChildHtml('group_tree');
    }

    /**
     * Retrieve Attribute Set Edit Form HTML
     *
     * @return string
     */
    public function getSetFormHtml()
    {
        return $this->getChildHtml('edit_set_form');
    }

    /**
     * Retrieve Block Header Text
     *
     * @return string
     */
    protected function _getHeader()
    {
        return __("Edit Attribute Set '%1'", $this->_getAttributeSet()->getAttributeSetName());
    }

    /**
     * Retrieve Attribute Set Save URL
     *
     * @return string
     */
    public function getMoveUrl()
    {
        return $this->getUrl('*/catalog_product_set/save', array('id' => $this->_getSetId()));
    }

    /**
     * Retrieve Attribute Set Group Save URL
     *
     * @return string
     */
    public function getGroupUrl()
    {
        return $this->getUrl('*/catalog_product_group/save', array('id' => $this->_getSetId()));
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getGroupTreeJson()
    {
        $items = array();
        $setId = $this->_getSetId();

        /* @var $groups \Magento\Eav\Model\Resource\Entity\Attribute\Group\Collection */
        $groups = $this->_groupFactory->create()
            ->getResourceCollection()
            ->setAttributeSetFilter($setId)
            ->setSortOrder()
            ->load();

        $configurable = $this->_attributeFactory->create()->getUsedAttributes($setId);

        $unassignableAttributes = $this->_catalogProduct->getUnassignableAttributes();

        /* @var $node \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($groups as $node) {
            $item = array();
            $item['text']       = $node->getAttributeGroupName();
            $item['id']         = $node->getAttributeGroupId();
            $item['cls']        = 'folder';
            $item['allowDrop']  = true;
            $item['allowDrag']  = true;

            $nodeChildren = $this->_collectionFactory->create()
                ->setAttributeGroupFilter($node->getId())
                ->addVisibleFilter()
                ->load();

            if ($nodeChildren->getSize() > 0) {
                $item['children'] = array();
                foreach ($nodeChildren->getItems() as $child) {
                    /* @var $child \Magento\Eav\Model\Entity\Attribute */

                    $isUnassignable = !in_array($child->getAttributeCode(), $unassignableAttributes);

                    $attr = array(
                        'text'              => $child->getAttributeCode(),
                        'id'                => $child->getAttributeId(),
                        'cls'               => $isUnassignable ? 'leaf' : 'system-leaf',
                        'allowDrop'         => false,
                        'allowDrag'         => true,
                        'leaf'              => true,
                        'is_user_defined'   => $child->getIsUserDefined(),
                        'is_configurable'   => (int)in_array($child->getAttributeId(), $configurable),
                        'is_unassignable'   => $isUnassignable,
                        'entity_id'         => $child->getEntityAttributeId()
                    );

                    $item['children'][] = $attr;
                }
            }

            $items[] = $item;
        }

        return $this->_coreData->jsonEncode($items);
    }

    /**
     * Retrieve Unused in Attribute Set Attribute Tree as JSON
     *
     * @return string
     */
    public function getAttributeTreeJson()
    {
        $items = array();
        $setId = $this->_getSetId();

        $collection = $this->_collectionFactory->create()
            ->setAttributeSetFilter($setId)
            ->load();

        $attributesIds = array('0');
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = $this->_collectionFactory->create()
            ->setAttributesExcludeFilter($attributesIds)
            ->addVisibleFilter()
            ->load();

        foreach ($attributes as $child) {
            $attr = array(
                'text'              => $child->getAttributeCode(),
                'id'                => $child->getAttributeId(),
                'cls'               => 'leaf',
                'allowDrop'         => false,
                'allowDrag'         => true,
                'leaf'              => true,
                'is_user_defined'   => $child->getIsUserDefined(),
                'is_configurable'   => false,
                'entity_id'         => $child->getEntityId()
            );

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = array(
                'text'      => __('Empty'),
                'id'        => 'empty',
                'cls'       => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            );
        }

        return $this->_coreData->jsonEncode($items);
    }

    /**
     * Retrieve Back Button HTML
     *
     * @return string
     */
    public function getBackButtonHtml()
    {
        return $this->getChildHtml('back_button');
    }

    /**
     * Retrieve Reset Button HTML
     *
     * @return string
     */
    public function getResetButtonHtml()
    {
        return $this->getChildHtml('reset_button');
    }

    /**
     * Retrieve Save Button HTML
     *
     * @return string
     */
    public function getSaveButtonHtml()
    {
        return $this->getChildHtml('save_button');
    }

    /**
     * Retrieve Delete Button HTML
     *
     * @return string
     */
    public function getDeleteButtonHtml()
    {
        if ($this->getIsCurrentSetDefault()) {
            return '';
        }
        return $this->getChildHtml('delete_button');
    }

    /**
     * Retrieve Delete Group Button HTML
     *
     * @return string
     */
    public function getDeleteGroupButton()
    {
        return $this->getChildHtml('delete_group_button');
    }

    /**
     * Retrieve Add New Group Button HTML
     *
     * @return string
     */
    public function getAddGroupButton()
    {
        return $this->getChildHtml('add_group_button');
    }

    /**
     * Retrieve Rename Button HTML
     *
     * @return string
     */
    public function getRenameButton()
    {
        return $this->getChildHtml('rename_button');
    }

    /**
     * Retrieve current Attribute Set object
     *
     * @return \Magento\Eav\Model\Entity\Attribute\Set
     */
    protected function _getAttributeSet()
    {
        return $this->_coreRegistry->registry('current_attribute_set');
    }

    /**
     * Retrieve current attribute set Id
     *
     * @return int
     */
    protected function _getSetId()
    {
        return $this->_getAttributeSet()->getId();
    }

    /**
     * Check Current Attribute Set is a default
     *
     * @return bool
     */
    public function getIsCurrentSetDefault()
    {
        $isDefault = $this->getData('is_current_set_default');
        if (is_null($isDefault)) {
            $defaultSetId = $this->_typeFactory->create()
                ->load($this->_coreRegistry->registry('entityType'))
                ->getDefaultAttributeSetId();
            $isDefault = $this->_getSetId() == $defaultSetId;
            $this->setData('is_current_set_default', $isDefault);
        }
        return $isDefault;
    }

    /**
     * Prepare HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $this->_eventManager->dispatch('adminhtml_catalog_product_attribute_set_main_html_before', array('block' => $this));
        return parent::_toHtml();
    }
}
