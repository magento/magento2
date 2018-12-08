<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
<<<<<<< HEAD
=======
     * Handles product tax attributes data initialization.
     *
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
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
