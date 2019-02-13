<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Attribute\Set;

use Magento\Catalog\Model\Entity\Product\Attribute\Group\AttributeMapperInterface;

/**
 * Adminhtml Catalog Attribute Set Main Block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Main extends \Magento\Backend\Block\Template
{
    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::catalog/product/attribute/set/main.phtml';

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory
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
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param AttributeMapperInterface $attributeMapper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Eav\Model\Entity\TypeFactory $typeFactory,
        \Magento\Eav\Model\Entity\Attribute\GroupFactory $groupFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        AttributeMapperInterface $attributeMapper,
        array $data = []
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

        $this->addChild('group_tree', \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Tree\Group::class);

        $this->addChild('edit_set_form', \Magento\Catalog\Block\Adminhtml\Product\Attribute\Set\Main\Formset::class);

        $this->addChild(
            'delete_group_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Delete Selected Group'), 'onclick' => 'editSet.submit();', 'class' => 'delete']
        );

        $this->addChild(
            'add_group_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Add New'), 'onclick' => 'editSet.addGroup();', 'class' => 'add']
        );

        $this->getToolbar()->addChild(
            'back_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Back'),
                'onclick' => 'setLocation(\'' . $this->getUrl('catalog/*/') . '\')',
                'class' => 'back'
            ]
        );

        $this->getToolbar()->addChild(
            'reset_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('Reset'), 'onclick' => 'window.location.reload()', 'class' => 'reset']
        );

        if (!$this->getIsCurrentSetDefault()) {
            $this->getToolbar()->addChild(
                'delete_button',
                \Magento\Backend\Block\Widget\Button::class,
                [
                    'label' => __('Delete'),
                    'onclick' => 'deleteConfirm(\'' . $this->escapeJsQuote(
                        __(
                            'You are about to delete all products in this attribute set. '
                            . 'Are you sure you want to do that?'
                        )
                    ) . '\', \'' . $this->getUrl(
                        'catalog/*/delete',
                        ['id' => $setId]
                    ) . '\', {data: {}})',
                    'class' => 'delete'
                ]
            );
        }

        $this->getToolbar()->addChild(
            'save_button',
            \Magento\Backend\Block\Widget\Button::class,
            [
                'label' => __('Save'),
                'onclick' => 'editSet.save();',
                'class' => 'save primary save-attribute-set'
            ]
        );

        $this->addChild(
            'rename_button',
            \Magento\Backend\Block\Widget\Button::class,
            ['label' => __('New Set Name'), 'onclick' => 'editSet.rename()']
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
     * @return \Magento\Framework\Phrase
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
        return $this->getUrl('catalog/product_set/save', ['id' => $this->_getSetId()]);
    }

    /**
     * Retrieve Attribute Set Group Save URL
     *
     * @return string
     */
    public function getGroupUrl()
    {
        return $this->getUrl('catalog/product_group/save', ['id' => $this->_getSetId()]);
    }

    /**
     * Retrieve Attribute Set Group Tree as JSON format
     *
     * @return string
     */
    public function getGroupTreeJson()
    {
        $items = [];
        $setId = $this->_getSetId();

        /* @var $groups \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection */
        $groups = $this->_groupFactory->create()->getResourceCollection()->setAttributeSetFilter(
            $setId
        )->setSortOrder()->load();

        /* @var $node \Magento\Eav\Model\Entity\Attribute\Group */
        foreach ($groups as $node) {
            $item = [];
            $item['text'] = $this->escapeHtml($node->getAttributeGroupName());
            $item['id'] = $node->getAttributeGroupId();
            $item['cls'] = 'folder';
            $item['allowDrop'] = true;
            $item['allowDrag'] = true;

            $nodeChildren = $this->_collectionFactory->create()->setAttributeGroupFilter(
                $node->getId()
            )->addVisibleFilter()->load();

            if ($nodeChildren->getSize() > 0) {
                $item['children'] = [];
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
        $items = [];
        $setId = $this->_getSetId();

        $collection = $this->_collectionFactory->create()->setAttributeSetFilter($setId)->load();

        $attributesIds = ['0'];
        /* @var $item \Magento\Eav\Model\Entity\Attribute */
        foreach ($collection->getItems() as $item) {
            $attributesIds[] = $item->getAttributeId();
        }

        $attributes = $this->_collectionFactory->create()->setAttributesExcludeFilter(
            $attributesIds
        )->addVisibleFilter()->load();

        foreach ($attributes as $child) {
            $attr = [
                'text' => $this->escapeHtml($child->getAttributeCode()),
                'id' => $child->getAttributeId(),
                'cls' => 'leaf',
                'allowDrop' => false,
                'allowDrag' => true,
                'leaf' => true,
                'is_user_defined' => $child->getIsUserDefined(),
                'entity_id' => $child->getEntityId(),
            ];

            $items[] = $attr;
        }

        if (count($items) == 0) {
            $items[] = [
                'text' => __('Empty'),
                'id' => 'empty',
                'cls' => 'folder',
                'allowDrop' => false,
                'allowDrag' => false,
            ];
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
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getIsCurrentSetDefault()
    {
        $isDefault = $this->getData('is_current_set_default');
        if ($isDefault === null) {
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
            ['block' => $this]
        );
        return parent::_toHtml();
    }
}
