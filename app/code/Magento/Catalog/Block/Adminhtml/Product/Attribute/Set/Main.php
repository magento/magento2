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
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set;

/**
 * Adminhtml Catalog Attribute Set Main Block
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;

class Main extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
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
     * @var \Magento\Framework\Registry
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
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var \Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface
     */
    protected $attributeMapper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Eav\Model\Entity\TypeFactory $typeFactory
     * @param \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory
     * @param \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param AttributeMapperInterface $attributeMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Catalog\Model\Resource\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        AttributeMapperInterface $attributeMapper,
        array $data = array()
    ) {
        $this->_jsonEncoder = $jsonEncoder;
        $this->_typeFactory = $typeFactory;
        $this->_groupFactory = $groupFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $this->attributeMapper = $attributeMapper;
        parent::__construct($context, $data);
    }

    /**
     * Prepare Global Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $setId = $this->_getSetId();

        $this->addChild('group_tree', 'Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Tree\Group');

        $this->addChild('edit_set_form', 'Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset');

        $this->addChild(
            'delete_group_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Delete Selected Group'), 'onclick' => 'editSet.submit();', 'class' => 'delete')
        );

        $this->addChild(
            'add_group_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Add New'), 'onclick' => 'editSet.addGroup();', 'class' => 'add')
        );

        $this->getToolbar()->addChild(
            'back_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/') . '\')',
                'class' => 'back'
            )
        );

        $this->getToolbar()->addChild(
            'reset_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('Reset'), 'onclick' => 'window.location.reload()', 'class' => 'reset')
        );

        if (!$this->getIsCurrentSetDefault()) {
            $this->getToolbar()->addChild(
                'delete_button',
                'Magento\Backend\Block\Widget\Button',
                array(
                    'label' => __('Delete Attribute Set'),
                    'onclick' => 'deleteConfirm(\'' . $this->escapeJsQuote(
                        __(
                            'You are about to delete all products in this set. ' .
                            'Are you sure you want to delete this attribute set?'
                        )
                    ) . '\', \'' . $this->getUrl(
                        'catalog/*/delete',
                        array('id' => $setId)
                    ) . '\')',
                    'class' => 'delete'
                )
            );
        }

        $this->getToolbar()->addChild(
            'save_button',
            'Magento\Backend\Block\Widget\Button',
            array(
                'label' => __('Save Attribute Set'),
                'onclick' => 'editSet.save();',
                'class' => 'save primary save-attribute-set'
            )
        );

        $this->addChild(
            'rename_button',
            'Magento\Backend\Block\Widget\Button',
            array('label' => __('New Set Name'), 'onclick' => 'editSet.rename()')
        );

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
        return $this->getUrl('catalog/product_set/save', array('id' => $this->_getSetId()));
    }

    /**
     * Retrieve Attribute Set Group Save URL
     *
     * @return string
     */
    public function getGroupUrl()
    {
        return $this->getUrl('catalog/product_group/save', array('id' => $this->_getSetId()));
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
        $groups = $this->_groupFactory->create()->getResourceCollection()->setAttributeSetFilter(
            $setId
        )->setSortOrder()->load();

        /* @var $node \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($groups as $node) {
            $item = array();
            $item['text'] = $node->getAttributeGroupName();
            $item['id'] = $node->getAttributeGroupId();
            $item['cls'] = 'folder';
            $item['allowDrop'] = true;
            $item['allowDrag'] = true;

            $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
                $node->getId()
            )->addVisibleFilter()->load();

            if ($nodeChildren->getSize() > 0) {
                $item['children'] = array();
                foreach ($nodeChildren->getItems() as $child) {
                    $item['children'][] = $this->attributeMapper->map($child);
                }
            }

            $items[] = $item;
        }

        return $this->_jsonEncoder->encode($items);
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

        $collection = $this->_collectionFactory->create()->setAttributeSetFilter($setId)->load();

        $attributesIds = array('0');
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = $this->_collectionFactory->create()->setAttributesExcludeFilter(
            $attributesIds
        )->addVisibleFilter()->load();

        foreach ($attributes as $child) {
            $attr = array(
                'text' => $child->getAttributeCode(),
                'id' => $child->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $child->getIsUserDefined(),
                'entity_id' => $child->getEntityId()
            );

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = array(
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false
            );
        }

        return $this->_jsonEncoder->encode($items);
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
            $defaultSetId = $this->_typeFactory->create()->load(
                $this->_coreRegistry->registry('entityType')
            )->getDefaultAttributeSetId();
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
        $this->_eventManager->dispatch(
            'adminhtml_catalog_product_attribute_set_main_html_before',
            array('block' => $this)
        );
        return parent::_toHtml();
    }
}
