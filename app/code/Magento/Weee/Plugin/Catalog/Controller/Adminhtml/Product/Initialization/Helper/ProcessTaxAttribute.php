<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Plugin\Catalog\Controller\Adminhtml\Product\Initialization\Helper;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper;
use Magento\Framework\App\RequestInterface;

/**
 * Handles product tax attributes data initialization.
 */
class ProcessTaxAttribute
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Handles product tax attributes data initialization.
     *
     * @param Helper $subject
     * @param Product $result
     * @param Product $product
     * @param array $productData
     * @return Product
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitializeFromData(
        Helper $subject,
        Product $result,
        Product $product,
        array $productData
    ): Product {
        $attributes = $result->getAttributes();
        if (!empty($attributes)) {
            foreach ($attributes as $attribute) {
                if ($attribute->getFrontendInput() == 'weee' && !isset($productData[$attribute->getAttributeCode()])) {
                    $result->setData($attribute->getAttributeCode(), []);
                }
            }
        }

        return $result;
    }
}
