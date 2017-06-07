<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Accordion;
use Magento\Backend\Block\Widget\Tabs as WigetTabs;
use Magento\Backend\Model\Auth\Session;
use Magento\Catalog\Helper\Catalog;
use Magento\Catalog\Helper\Data;
use Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;

/**
 * Admin product edit tabs
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Tabs extends WigetTabs
{
    const BASIC_TAB_GROUP_CODE = 'basic';

    const ADVANCED_TAB_GROUP_CODE = 'advanced';

    /**
     * @var string
     */
    protected $_attributeTabBlock = \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes::class;

    /**
     * @var string
     */
    protected $_template = 'Magento_Catalog::product/edit/tabs.phtml';

    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * Adminhtml catalog
     *
     * @var Catalog
     */
    protected $_helperCatalog = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var Manager
     */
    protected $_moduleManager;

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Manager $moduleManager
     * @param CollectionFactory $collectionFactory
     * @param Catalog $helperCatalog
     * @param Data $catalogData
     * @param Registry $registry
     * @param InlineInterface $translateInline
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Manager $moduleManager,
        CollectionFactory $collectionFactory,
        Catalog $helperCatalog,
        Data $catalogData,
        Registry $registry,
        InlineInterface $translateInline,
        array $data = []
    ) {
        $this->_moduleManager = $moduleManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_helperCatalog = $helperCatalog;
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_info_tabs');
        $this->setDestElementId('product-edit-form-tabs');
    }

    /**
     * @param int $attributeSetId
     * @return \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\Collection
     */
    public function getGroupCollection($attributeSetId)
    {
        return $this->_collectionFactory->create()
            ->setAttributeSetFilter($attributeSetId)
            ->setSortOrder()
            ->load();
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareLayout()
    {
        $product = $this->getProduct();

        if (!($setId = $product->getAttributeSetId())) {
            $setId = $this->getRequest()->getParam('set', null);
        }

        if ($setId) {
            $tabAttributesBlock = $this->getLayout()->createBlock(
                $this->getAttributeTabBlock(),
                $this->getNameInLayout() . '_attributes_tab'
            );
            $advancedGroups = [];

            foreach ($this->getGroupCollection($setId) as $group) {
                /** @var $group \Magento\Eav\Model\Entity\Attribute\Group*/
                $attributes = $product->getAttributes($group->getId(), true);

                foreach ($attributes as $key => $attribute) {
                    $applyTo = $attribute->getApplyTo();
                    if (!$attribute->getIsVisible() || !empty($applyTo) && !in_array($product->getTypeId(), $applyTo)
                    ) {
                        unset($attributes[$key]);
                    }
                }

                if ($attributes) {
                    $tabData = [
                        'label' => __($group->getAttributeGroupName()),
                        'content' => $this->_translateHtml(
                            $tabAttributesBlock->setGroup($group)->setGroupAttributes($attributes)->toHtml()
                        ),
                        'class' => 'user-defined',
                        'group_code' => $group->getTabGroupCode() ?: self::BASIC_TAB_GROUP_CODE,
                    ];

                    if ($tabData['group_code'] === self::BASIC_TAB_GROUP_CODE) {
                        $this->addTab($group->getAttributeGroupCode(), $tabData);
                    } else {
                        $advancedGroups[$group->getAttributeGroupCode()] = $tabData;
                    }
                }
            }

            /* Don't display website tab for single mode */
            if (!$this->_storeManager->isSingleStoreMode()) {
                $this->addTab(
                    'websites',
                    [
                        'label' => __('Websites'),
                        'content' => $this->_translateHtml(
                            $this->getLayout()->createBlock(
                                \Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Websites::class
                            )->toHtml()
                        ),
                        'group_code' => self::BASIC_TAB_GROUP_CODE
                    ]
                );
            }

            if (isset($advancedGroups['advanced-pricing'])) {
                $this->addTab('advanced-pricing', $advancedGroups['advanced-pricing']);
                unset($advancedGroups['advanced-pricing']);
            }

            if ($this->_moduleManager->isEnabled('Magento_CatalogInventory')
                && $this->getChildBlock('advanced-inventory')
            ) {
                $this->addTab(
                    'advanced-inventory',
                    [
                        'label' => __('Advanced Inventory'),
                        'content' => $this->_translateHtml(
                            $this->getChildHtml('advanced-inventory')
                        ),
                        'group_code' => self::ADVANCED_TAB_GROUP_CODE
                    ]
                );
            }

            /**
             * Do not change this tab id
             */
            if ($this->getChildBlock('customer_options')) {
                $this->addTab('customer_options', 'customer_options');
                $this->getChildBlock('customer_options')->setGroupCode(self::ADVANCED_TAB_GROUP_CODE);
            }

            $this->addTab(
                'related',
                [
                    'label' => __('Related Products'),
                    'url' => $this->getUrl('catalog/*/related', ['_current' => true]),
                    'class' => 'ajax',
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE
                ]
            );

            $this->addTab(
                'upsell',
                [
                    'label' => __('Up-sells'),
                    'url' => $this->getUrl('catalog/*/upsell', ['_current' => true]),
                    'class' => 'ajax',
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE
                ]
            );

            $this->addTab(
                'crosssell',
                [
                    'label' => __('Cross-sells'),
                    'url' => $this->getUrl('catalog/*/crosssell', ['_current' => true]),
                    'class' => 'ajax',
                    'group_code' => self::ADVANCED_TAB_GROUP_CODE
                ]
            );

            if (isset($advancedGroups['design'])) {
                $this->addTab('design', $advancedGroups['design']);
                unset($advancedGroups['design']);
            }

            if ($this->getChildBlock('product-alerts')) {
                $this->addTab('product-alerts', 'product-alerts');
                $this->getChildBlock('product-alerts')->setGroupCode(self::ADVANCED_TAB_GROUP_CODE);
            }

            if (isset($advancedGroups['autosettings'])) {
                $this->addTab('autosettings', $advancedGroups['autosettings']);
                unset($advancedGroups['autosettings']);
            }

            foreach ($advancedGroups as $groupCode => $group) {
                $this->addTab($groupCode, $group);
            }
        }

        return parent::_prepareLayout();
    }

    /**
     * Check whether active tab belong to advanced group
     *
     * @return bool
     */
    public function isAdvancedTabGroupActive()
    {
        return $this->_tabs[$this->_activeTab]->getGroupCode() == self::ADVANCED_TAB_GROUP_CODE;
    }

    /**
     * Retrieve product object from object if not from registry
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->getData('product') instanceof \Magento\Catalog\Model\Product) {
            $this->setData('product', $this->_coreRegistry->registry('product'));
        }
        return $this->getData('product');
    }

    /**
     * Getting attribute block name for tabs
     *
     * @return string
     */
    public function getAttributeTabBlock()
    {
        if ($this->_helperCatalog->getAttributeTabBlock() === null) {
            return $this->_attributeTabBlock;
        }
        return $this->_helperCatalog->getAttributeTabBlock();
    }

    /**
     * @param string $attributeTabBlock
     * @return $this
     */
    public function setAttributeTabBlock($attributeTabBlock)
    {
        $this->_attributeTabBlock = $attributeTabBlock;
        return $this;
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }

    /**
     * @param string $parentTab
     * @return string
     */
    public function getAccordion($parentTab)
    {
        $html = '';
        foreach ($this->_tabs as $childTab) {
            if ($childTab->getParentTab() === $parentTab->getId()) {
                $html .= $this->getChildBlock('child-tab')->setTab($childTab)->toHtml();
            }
        }
        return $html;
    }
}
