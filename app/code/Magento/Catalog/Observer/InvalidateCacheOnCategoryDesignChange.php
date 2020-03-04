<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

/**
 * Observer for invalidating cache on catalog category design change
 */
class InvalidateCacheOnCategoryDesignChange implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    private $cacheTypeList;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get default category design attribute values
     *
     * @return array
     */
    private function getDefaultAttributeValues()
    {
        return [
            'custom_apply_to_products' => '0',
            'custom_use_parent_settings' => '0',
            'page_layout' => $this->scopeConfig->getValue(
                'web/default_layouts/default_category_layout',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        ];
    }

    /**
     * Invalidate cache on category design attribute value changed
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(Observer $observer)
    {
        $category = $observer->getEvent()->getEntity();
        if (!$category->isObjectNew()) {
            foreach ($category->getDesignAttributes() as $designAttribute) {
                if ($this->isCategoryAttributeChanged($designAttribute->getAttributeCode(), $category)) {
                    $this->cacheTypeList->invalidate(
                        [
                            \Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER,
                            \Magento\Framework\App\Cache\Type\Layout::TYPE_IDENTIFIER
                        ]
                    );
                    break;
                }
            }
        }
    }

    /**
     * Check if category attribute changed
     *
     * @param string $attributeCode
     * @param \Magento\Catalog\Api\Data\CategoryInterface $category
     * @return bool
     */
    private function isCategoryAttributeChanged($attributeCode, $category)
    {
        if (!array_key_exists($attributeCode, $category->getOrigData())) {
            $defaultValue = $this->getDefaultAttributeValues()[$attributeCode] ?? null;
            if ($category->getData($attributeCode) !== $defaultValue) {
                return true;
            }
        } else {
            if ($category->dataHasChangedFor($attributeCode)) {
                return true;
            }
        }

        return false;
    }
}
