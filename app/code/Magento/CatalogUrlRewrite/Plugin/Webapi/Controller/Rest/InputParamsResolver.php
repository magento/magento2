<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\Router\Route;

/**
 * Plugin for InputParamsResolver
 *
 * Used to modify product data with save_rewrites_history flag
 */
class InputParamsResolver
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
     * Add 'save_rewrites_history' param to the product and category data
     *
     * @see \Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(\Magento\Webapi\Controller\Rest\InputParamsResolver $subject, array $result): array
    {
        $this->processProductCall($subject, $result);
        $this->processCategoryCall($subject, $result);

        return $result;
    }

    /**
     * Check that product save method called
     *
     * @param Route $route
     * @return bool
     */
    private function isProductSaveCalled(Route $route): bool
    {
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        return $serviceClassName === ProductRepositoryInterface::class && $serviceMethodName === 'save';
    }

    /**
     * Check that category save method called
     *
     * @param Route $route
     * @return bool
     */
    private function isCategorySaveCalled(Route $route): bool
    {
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();

        return $serviceClassName === CategoryRepositoryInterface::class && $serviceMethodName === 'save';
    }

    /**
     * Check is any custom options exists in product data
     *
     * @param array $requestBodyParams
     * @param string $entityCode
     * @return bool
     */
    private function isCustomAttributesExists(array $requestBodyParams, string $entityCode): bool
    {
        return !empty($requestBodyParams[$entityCode]['custom_attributes']);
    }

    /**
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $subject
     * @param array $result
     * @return array
     */
    private function processProductCall(\Magento\Webapi\Controller\Rest\InputParamsResolver $subject, array $result): void
    {
        $requestBodyParams = $this->request->getBodyParams();

        if ($this->isProductSaveCalled($subject->getRoute())
            && $this->isCustomAttributesExists($requestBodyParams, 'product')) {
            foreach ($requestBodyParams['product']['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === self::SAVE_REWRITES_HISTORY) {
                    foreach ($result as $resultItem) {
                        if ($resultItem instanceof \Magento\Catalog\Model\Product) {
                            $resultItem->setData(self::SAVE_REWRITES_HISTORY, (bool)$attribute['value']);
                            break 2;
                        }
                    }
                    break;
                }
            }
        }
    }

    /**
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $subject
     * @param array $result
     */
    private function processCategoryCall(\Magento\Webapi\Controller\Rest\InputParamsResolver $subject, array $result): void
    {
        $requestBodyParams = $this->request->getBodyParams();

        if ($this->isCategorySaveCalled($subject->getRoute())
            && $this->isCustomAttributesExists($requestBodyParams, 'category')) {
            foreach ($requestBodyParams['category']['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === self::SAVE_REWRITES_HISTORY) {
                    foreach ($result as $resultItem) {
                        if ($resultItem instanceof \Magento\Catalog\Model\Category) {
                            $resultItem->setData(self::SAVE_REWRITES_HISTORY, (bool)$attribute['value']);
                            break 2;
                        }
                    }
                    break;
                }
            }
        }
    }
}
