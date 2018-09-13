<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\Resolver;

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldName\ResolverInterface;

/**
 * Resolver field name for Category name attribute.
 */
class CategoryName implements ResolverInterface
{
    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        StoreManager $storeManager,
        Registry $coreRegistry
    ) {
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = [])
    {
        return 'name_category_' . $this->resolveCategoryId($context);
    }

    /**
     * Resolve category id.
     *
     * @param array $context
     * @return int
     */
    private function resolveCategoryId($context)
    {
        if (isset($context['categoryId'])) {
            $id = $context['categoryId'];
        } else {
            $id = $this->coreRegistry->registry('current_category')
                ? $this->coreRegistry->registry('current_category')->getId()
                : $this->storeManager->getStore()->getRootCategoryId();
        }

        return $id;
    }
}
