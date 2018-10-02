<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\Resolver;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\AttributeAdapter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface as StoreManager;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldName\ResolverInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param StoreManager $storeManager
     * @param Registry $coreRegistry
     */
    public function __construct(
        LoggerInterface $logger,
        StoreManager $storeManager = null,
        Registry $coreRegistry = null
    ) {
        $this->logger = $logger;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManager::class);
        $this->coreRegistry = $coreRegistry ?: ObjectManager::getInstance()
            ->get(Registry::class);
    }

    /**
     * Get field name.
     *
     * @param AttributeAdapter $attribute
     * @param array $context
     * @return string
     */
    public function getFieldName(AttributeAdapter $attribute, $context = []): ?string
    {
        if ($attribute->getAttributeCode() === 'category_name') {
            return 'name_category_' . $this->resolveCategoryId($context);
        }

        return null;
    }

    /**
     * Resolve category id.
     *
     * @param array $context
     * @return int
     */
    private function resolveCategoryId($context): int
    {
        if (isset($context['categoryId'])) {
            $id = $context['categoryId'];
        } else {
            $id = \Magento\Catalog\Model\Category::ROOT_CATEGORY_ID;
            try {
                $id = $this->coreRegistry->registry('current_category')
                    ? $this->coreRegistry->registry('current_category')->getId()
                    : $this->storeManager->getStore()->getRootCategoryId();
            } catch (LocalizedException $exception) {
                $this->logger->critical($exception);
            }
        }

        return $id;
    }
}
