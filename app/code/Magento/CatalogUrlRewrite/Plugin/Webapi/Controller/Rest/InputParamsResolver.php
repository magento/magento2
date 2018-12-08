<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\CatalogUrlRewrite\Plugin\Webapi\Controller\Rest;

use Magento\Catalog\Api\ProductRepositoryInterface;
<<<<<<< HEAD
use Magento\Catalog\Model\Product;
use Magento\Framework\Webapi\Rest\Request as RestRequest;
use Magento\Webapi\Controller\Rest\InputParamsResolver as InputParamsResolverController;
=======
use Magento\Framework\Webapi\Rest\Request as RestRequest;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3

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
<<<<<<< HEAD
     * @param InputParamsResolverController $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(InputParamsResolverController $subject, array $result): array
=======
     * @param \Magento\Webapi\Controller\Rest\InputParamsResolver $subject
     * @param array $result
     * @return array
     */
    public function afterResolve(\Magento\Webapi\Controller\Rest\InputParamsResolver $subject, array $result): array
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
                        if ($resultItem instanceof Product) {
=======
                        if ($resultItem instanceof \Magento\Catalog\Model\Product) {
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
