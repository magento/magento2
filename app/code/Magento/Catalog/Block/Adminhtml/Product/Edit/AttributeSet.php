<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Create product attribute set selector
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Catalog\Block\Adminhtml\Product\Edit;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Json\Helper\Data as JsonHelper;

/**
 * Admin AttributeSet block
 */
class AttributeSet extends \Magento\Backend\Block\Widget\Form
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     * @param JsonHelper|null $jsonHelper
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = [],
        ?JsonHelper $jsonHelper = null
    ) {
        $this->_coreRegistry = $registry;
        $data['jsonHelper'] = $jsonHelper ?? ObjectManager::getInstance()->get(JsonHelper::class);
        parent::__construct($context, $data);
    }

    /**
     * Get options for suggest widget
     *
     * @return array
     */
    public function getSelectorOptions()
    {
        return [
            'source' => $this->escapeUrl($this->getUrl('catalog/product/suggestAttributeSets')),
            'className' => 'category-select',
            'showRecent' => true,
            'storageKey' => 'product-template-key',
            'minLength' => 0,
            'currentlySelected' => $this->escapeHtml(
                $this->_coreRegistry->registry('product')->getAttributeSetId()
            )
        ];
    }
}
