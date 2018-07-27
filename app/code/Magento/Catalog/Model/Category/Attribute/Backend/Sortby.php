<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Category\Attribute\Backend;

/**
 * Catalog Category Attribute Default and Available Sort By Backend Model
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Sortby extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * Core store config
     *
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Construct
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->_scopeConfig = $scopeConfig;
    }

    /**
     * Validate process
     *
     * @param \Magento\Framework\DataObject $object
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $postDataConfig = $object->getData('use_post_data_config') ?: [];
        $isUseConfig = in_array($attributeCode, $postDataConfig);

        if ($this->getAttribute()->getIsRequired()) {
            $attributeValue = $object->getData($attributeCode);
            if ($this->getAttribute()->isValueEmpty($attributeValue) && !$isUseConfig) {
                return false;
            }
        }

        if ($this->getAttribute()->getIsUnique()) {
            if (!$this->getAttribute()->getEntity()->checkAttributeUniqueValue($this->getAttribute(), $object)) {
                $label = $this->getAttribute()->getFrontend()->getLabel();
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The value of attribute "%1" must be unique.', $label)
                );
            }
        }

        if ($attributeCode == 'default_sort_by') {
            $available = $object->getData('available_sort_by') ?: [];
            $available = is_array($available) ? $available : explode(',', $available);
            $data = !in_array(
                'default_sort_by',
                $postDataConfig
            ) ? $object->getData(
                $attributeCode
            ) : $this->_scopeConfig->getValue(
                "catalog/frontend/default_sort_by",
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
            if (!in_array($data, $available) && !in_array('available_sort_by', $postDataConfig)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Default Product Listing Sort by does not exist in Available Product Listing Sort By.')
                );
            }
        }

        return true;
    }

    /**
     * Before Attribute Save Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function beforeSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'available_sort_by') {
            $data = $object->getData($attributeCode);
            if (!is_array($data)) {
                $data = [];
            }
            $object->setData($attributeCode, implode(',', $data) ?: null);
        }
        if (!$object->hasData($attributeCode)) {
            $object->setData($attributeCode, null);
        }
        return $this;
    }

    /**
     * After Load Attribute Process
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        if ($attributeCode == 'available_sort_by') {
            $data = $object->getData($attributeCode);
            if ($data) {
                if (!is_array($data)) {
                    $object->setData($attributeCode, explode(',', $data));
                } else {
                    $object->setData($attributeCode, $data);
                }

            }
        }
        return $this;
    }
}
