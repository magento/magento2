<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Downloadable\Model\Product\TypeTransitionManager\Plugin;

use Closure;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Model\Product\Edit\WeightResolver;

/**
 * Plugin for product type transition manager
 */
class Downloadable
{
    /**
     * Request instance
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Model\Product\Edit\WeightResolver
     */
    protected $weightResolver;

    /**
     * @param RequestInterface $request
     * @param WeightResolver $weightResolver
     */
    public function __construct(RequestInterface $request, WeightResolver $weightResolver)
    {
        $this->request = $request;
        $this->weightResolver = $weightResolver;
    }

    /**
     * Change product type to downloadable if needed
     *
     * @param \Magento\Catalog\Model\Product\TypeTransitionManager $subject
     * @param Closure $proceed
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundProcessProduct(
        \Magento\Catalog\Model\Product\TypeTransitionManager $subject,
        Closure $proceed,
        \Magento\Catalog\Model\Product $product
    ) {
        $isTypeCompatible = in_array(
            $product->getTypeId(),
            [
                \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE,
                \Magento\Catalog\Model\Product\Type::TYPE_VIRTUAL,
                \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE
            ]
        );
        $downloadableData = $this->request->getPost('downloadable');
        $hasDownloadableData = false;
        if (isset($downloadableData)) {
            foreach ($downloadableData as $data) {
                foreach ($data as $rowData) {
                    if (empty($rowData['is_delete'])) {
                        $hasDownloadableData = true;
                        break 2;
                    }
                }
            }
        }
        if ($isTypeCompatible && $hasDownloadableData && !$this->weightResolver->resolveProductHasWeight($product)) {
            $product->setTypeId(\Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE);
            return;
        }
        $proceed($product);
    }
}
