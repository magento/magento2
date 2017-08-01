<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\CopyConstructor;

use Magento\Catalog\Model\Product\CopyConstructorFactory;
use Magento\Catalog\Model\Product\CopyConstructorInterface;

/**
 * Performs duplication process by applying product type convertors
 *
 * @api
 * @since 2.0.0
 */
class Composite implements CopyConstructorInterface
{
    /**
     * @var CopyConstructorInterface[]
     * @since 2.0.0
     */
    protected $constructors;

    /**
     * @param CopyConstructorFactory $factory
     * @param string[] $constructors
     * @since 2.0.0
     */
    public function __construct(CopyConstructorFactory $factory, array $constructors = [])
    {
        foreach ($constructors as $instance) {
            $this->constructors[] = $factory->create($instance);
        }
    }

    /**
     * Build product duplicate
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $duplicate
     * @return void
     * @since 2.0.0
     */
    public function build(\Magento\Catalog\Model\Product $product, \Magento\Catalog\Model\Product $duplicate)
    {
        foreach ($this->constructors as $constructor) {
            $constructor->build($product, $duplicate);
        }
    }
}
