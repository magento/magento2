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
class CategoryName extends Resolver implements ResolverInterface
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
     * @param ResolverInterface $resolver
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        ResolverInterface $resolver,
        StoreManager $storeManager,
        Registry $coreRegistry
    ) {
        parent::__construct($resolver);
        $this->storeManager = $storeManager;
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldName($attributeCode, $context = []): string
    {
        if ($attributeCode === 'category_name') {
            return 'name_category_' . $this->resolveCategoryId($context);
        }

        return $this->getNext()->getFieldName($attributeCode, $context);
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
