<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Model;

use Magento\Catalog\Model\Category;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

class CategorySetSaveRewriteHistory
{
    private const SAVE_REWRITES_HISTORY = 'save_rewrites_history';

    /**
     * @var RestRequest
     */
    private $request;

    /**
     * @param RestRequest $request
     */
    public function __construct(RestRequest $request)
    {
        $this->request = $request;
    }

    /**
     * Add 'save_rewrites_history' param to the category for list
     *
     * @param CategoryUrlRewriteGenerator $subject
     * @param Category $category
     * @param bool $overrideStoreUrls
     * @param int|null $rootCategoryId
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGenerate(
        CategoryUrlRewriteGenerator $subject,
        Category $category,
        bool $overrideStoreUrls = false,
        ?int $rootCategoryId = null
    ) {
        $requestBodyParams = $this->request->getBodyParams();

        if ($this->isCustomAttributesExists($requestBodyParams, CategoryUrlRewriteGenerator::ENTITY_TYPE)) {
            foreach ($requestBodyParams[CategoryUrlRewriteGenerator::ENTITY_TYPE]['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === self::SAVE_REWRITES_HISTORY) {
                    $category->setData(self::SAVE_REWRITES_HISTORY, (bool)$attribute['value']);
                }
            }
        }

        return [$category, $overrideStoreUrls, $rootCategoryId];
    }

    /**
     * Check is any custom options exists in data
     *
     * @param array $requestBodyParams
     * @param string $entityCode
     * @return bool
     */
    private function isCustomAttributesExists(array $requestBodyParams, string $entityCode): bool
    {
        return !empty($requestBodyParams[$entityCode]['custom_attributes']);
    }
}
