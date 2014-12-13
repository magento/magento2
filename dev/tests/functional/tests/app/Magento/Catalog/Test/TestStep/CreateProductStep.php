<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Catalog\Test\TestStep;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\TestStep\TestStepInterface;

/**
 * Create product using handler.
 */
class CreateProductStep implements TestStepInterface
{
    /**
     * Product fixture from dataSet.
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
        list($fixtureClass, $dataSet) = explode('::', $this->product);
        /** @var FixtureInterface $product */
        $product = $this->fixtureFactory->createByCode(trim($fixtureClass), ['dataSet' => trim($dataSet)]);
        if ($product->hasData('id') === false) {
            $product->persist();
        }

        return ['product' => $product];
    }
}
