<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Block\Product\View;

/**
 * Product view abstract block
 *
 * @api
 * @deprecated 2.2.0
 * @since 2.0.0
 */
abstract class AbstractView extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Magento\Framework\Stdlib\ArrayUtils
     * @since 2.0.0
     */
    protected $arrayUtils;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->arrayUtils = $arrayUtils;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Retrieve product
     *
     * @return \Magento\Catalog\Model\Product
     * @since 2.0.0
     */
    public function getProduct()
    {
        $product = parent::getProduct();
        if ($product && $product->getTypeInstance()->getStoreFilter($product) === null) {
            $product->getTypeInstance()->setStoreFilter($this->_storeManager->getStore(), $product);
        }
        return $product;
    }

    /**
     * Decorate a plain array of arrays or objects
     *
     * @param array $array
     * @param string $prefix
     * @param bool $forceSetAll
     * @return array
     * @since 2.0.0
     */
    public function decorateArray($array, $prefix = 'decorated_', $forceSetAll = false)
    {
        return $this->arrayUtils->decorateArray($array, $prefix, $forceSetAll);
    }
}
