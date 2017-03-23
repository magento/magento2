<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\TestStep;

use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Create product using handler.
 */
class CreateProductStep implements TestStepInterface
{
    /**
     * Product fixture from dataset.
     *
     * @var string
     */
    protected $product;

    /**
     * Factory for Fixtures.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties.
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $product
     */
    public function __construct(FixtureFactory $fixtureFactory, $product)
    {
        $this->product = $product;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create product.
     *
     * @return array
     */
    public function run()
    {
        list($fixtureClass, $dataset) = explode('::', $this->product);
        /** @var FixtureInterface $product */
        $product = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataset' => trim($dataset)]);
        if ($product->hasData('id') === false) {
            $product->persist();
        }
        return ['product' => $product];
    }
}
