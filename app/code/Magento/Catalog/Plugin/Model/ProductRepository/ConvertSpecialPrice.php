<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Plugin\Model\ProductRepository;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Pricing\Price\SpecialPrice;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Api\CustomAttributesDataInterface;
use Magento\Framework\Webapi\Rest\Request;

/**
 * Convert product special price for REST API.
 */
class ConvertSpecialPrice
{
    /**
     * Provide original body request.
     *
     * @var Request
     */
    private $request;

    /**
     * ConvertSpecialPrice constructor.
     *
     * @param Request $request
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Convert special price for REST api.
     *
     * Check product special price from request. If special price = ''(remove special price from product),
     * convert it from 0 back to '', in order to save product without special price, but not with 0.00.
     *
     * @param ProductRepositoryInterface $subject
     * @param ProductInterface $product
     * @param bool $params
     * @return null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave(ProductRepositoryInterface $subject, ProductInterface $product, bool $params = false)
    {
        $bodyParams = $this->request->getBodyParams();
        if (isset($bodyParams['product'][CustomAttributesDataInterface::CUSTOM_ATTRIBUTES])) {
            foreach ($bodyParams['product'][CustomAttributesDataInterface::CUSTOM_ATTRIBUTES] as $attribute) {
                if ($attribute[AttributeInterface::ATTRIBUTE_CODE] === SpecialPrice::PRICE_CODE
                    && $attribute[AttributeInterface::VALUE] === '') {
                    $product->setCustomAttribute(SpecialPrice::PRICE_CODE, '');
                }
            }
        }

        return null;
    }
}
