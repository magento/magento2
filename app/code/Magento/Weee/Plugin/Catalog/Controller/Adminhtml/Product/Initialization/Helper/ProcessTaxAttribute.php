<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
<<<<<<< HEAD
=======
declare(strict_types=1);

>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
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
