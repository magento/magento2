<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * New attribute panel on product edit page
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit\Tab\Attributes;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Admin product attribute search block
 */
class Search extends \Magento\Backend\Block\Widget
{
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
     * @var \Magento\Framework\DB\Helper
     */
    protected $_resourceHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\DB\Helper $resourceHelper
     * @param \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\DB\Helper $resourceHelper,
        \Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory $collectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = [],
        ?JsonHelper $jsonHelper = null
    ) {
        $this->_resourceHelper = $resourceHelper;
        $this->_collectionFactory = $collectionFactory;
        $this->_coreRegistry = $registry;
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Define block template
     *
     * @return void
     */
    protected function _construct()
    {
        $this->setTemplate('Magento_Catalog::product/edit/attribute/search.phtml');
        parent::_construct();
    }

    /**
     * Get selector options
     *
     * @return array
     */
    public function getSelectorOptions()
    {
        $templateId = $this->_coreRegistry->registry('product')->getAttributeSetId();
        return [
            'source' => $this->escapeUrl($this->getUrl('catalog/product/suggestAttributes')),
            'minLength' => 0,
            'ajaxOptions' => ['data' => ['template_id' => $templateId]],
            'template' => '[data-template-for="product-attribute-search-' . $this->getGroupId() . '"]',
            'data' => $this->getSuggestedAttributes('', $templateId)
        ];
    }

    /**
     * Retrieve list of attributes with admin store label containing $labelPart
     *
     * @param string $labelPart
     * @param int $templateId
     * @return array
     */
    public function getSuggestedAttributes($labelPart, $templateId = null)
    {
        $escapedLabelPart = $this->_resourceHelper->addLikeEscape(
            $labelPart,
            ['position' => 'any']
        );
        /** @var $collection \Magento\Catalog\Model\ResourceModel\Product\Attribute\Collection */
        $collection = $this->_collectionFactory->create()->addFieldToFilter(
            'frontend_label',
            ['like' => $escapedLabelPart]
        );

        $collection->setExcludeSetFilter($templateId ?: $this->getRequest()->getParam('template_id'))->setPageSize(20);

        $result = [];
        foreach ($collection->getItems() as $attribute) {
            /** @var $attribute \Magento\Catalog\Model\ResourceModel\Eav\Attribute */
            $result[] = [
                'id' => $attribute->getId(),
                'label' => $attribute->getFrontendLabel(),
                'code' => $attribute->getAttributeCode(),
            ];
        }
        return $result;
    }

    /**
     * Get add attribute url
     *
     * @return string
     */
    public function getAddAttributeUrl()
    {
        return $this->getUrl('catalog/product/addAttributeToTemplate');
    }
}
