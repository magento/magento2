<?php
/**
 * Plugin for product type transition manager
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin;

use Closure;
use Magento\Framework\App\RequestInterface;

/**
 * Class \Magento\ConfigurableProduct\Model\Product\TypeTransitionManager\Plugin\Configurable
 *
 * @since 2.0.0
 */
class Configurable
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     * @since 2.0.0
     */
    protected $request;

    /**
     * @param RequestInterface $request
     * @since 2.0.0
     */
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }

    /**
     * Change product type to configurable if needed
     *
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $subject
     * @param Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function aroundProcessProduct(
        \Magento\Catalog\Model\Product\TypeTransitionManager $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $attributes = $this->request->getParam('attributes');
        if (!empty($attributes)) {
            $product->setTypeId(\Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE);
            return;
        }
        $proceed($product);
    }
}
