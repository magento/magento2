<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

/**
 * Product attribute source input types
 */
namespace Magento\Catalog\Model\Product\Attribute\Source;

/**
 * Class \Magento\Catalog\Model\Product\Attribute\Source\Inputtype
 *
 * @since 2.0.0
 */
class Inputtype extends \Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     * @since 2.0.0
     */
    protected $_eventManager = null;

    /**
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\Registry $coreRegistry
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Event\ManagerInterface $eventManager, \Magento\Framework\Registry $coreRegistry)
    {
        $this->_eventManager = $eventManager;
        $this->_coreRegistry = $coreRegistry;
    }

    /**
     * Get product input types as option array
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @since 2.0.0
     */
    public function toOptionArray()
    {
        $inputTypes = [
            ['value' => 'price', 'label' => __('Price')],
            ['value' => 'media_image', 'label' => __('Media Image')],
        ];

        $response = new \Magento\Framework\DataObject();
        $response->setTypes([]);
        $this->_eventManager->dispatch('adminhtml_product_attribute_types', ['response' => $response]);
        $_hiddenFields = [];
        foreach ($response->getTypes() as $type) {
            $inputTypes[] = $type;
            if (isset($type['hide_fields'])) {
                $_hiddenFields[$type['value']] = $type['hide_fields'];
            }
        }

        if ($this->_coreRegistry->registry('attribute_type_hidden_fields') === null) {
            $this->_coreRegistry->register('attribute_type_hidden_fields', $_hiddenFields);
        }
        return array_merge(parent::toOptionArray(), $inputTypes);
    }
}
