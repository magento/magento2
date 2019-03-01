<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\InputParamsResolver as InputParamsResolverController;

/**
 * Plugin for InputParamsResolver
 *
 * Used to modify product data with save_rewrites_history flag
 */
class InputParamsResolver
{
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
     * Add 'save_rewrites_history' param to the product data
     *
     * @see \Magento\CatalogUrlRewrite\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper
     * @param InputParamsResolverController $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(InputParamsResolverController $subject, array $result): array
    {
        $route = $subject->getRoute();
        $serviceMethodName = $route->getServiceMethod();
        $serviceClassName = $route->getServiceClass();
        $requestBodyParams = $this->request->getBodyParams();

        if ($this->isProductSaveCalled($serviceClassName, $serviceMethodName)
            && $this->isCustomAttributesExists($requestBodyParams)) {
            foreach ($requestBodyParams['product']['custom_attributes'] as $attribute) {
                if ($attribute['attribute_code'] === 'save_rewrites_history') {
                    foreach ($result as $resultItem) {
                        if ($resultItem instanceof Product) {
                            $resultItem->setData('save_rewrites_history', (bool)$attribute['value']);
                            break 2;
                        }
                    }
                    break;
                }
            }
        }
        return $result;
    }

    /**
     * Check that product save method called
     *
     * @param string $serviceClassName
     * @param string $serviceMethodName
     * @return bool
     */
    private function isProductSaveCalled(string $serviceClassName, string $serviceMethodName): bool
    {
        return $serviceClassName === ProductRepositoryInterface::class && $serviceMethodName === 'save';
    }

    /**
     * Check is any custom options exists in product data
     *
     * @param array $requestBodyParams
     * @return bool
     */
    private function isCustomAttributesExists(array $requestBodyParams): bool
    {
        return !empty($requestBodyParams['product']['custom_attributes']);
    }
}
