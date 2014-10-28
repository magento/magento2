<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Test\TestStep;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;
use Mtf\TestStep\TestStepInterface;

/**
 * Class CreateProductsStep
 * Create products using handler
 */
class CreateProductsStep implements TestStepInterface
{
    /**
     * Products names in data set
     *
     * @var string
     */
    protected $products;

    /**
     * Factory for Fixtures
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Preparing step properties
     *
     * @constructor
     * @param FixtureFactory $fixtureFactory
     * @param string $products
     */
    public function __construct(FixtureFactory $fixtureFactory, $products)
    {
        $this->products = $products;
        $this->fixtureFactory = $fixtureFactory;
    }

    /**
     * Create products
     *
     * @return array
     */
    public function run()
    {
        $products = [];
        $productsDataSets = explode(',', $this->products);
        foreach ($productsDataSets as $key => $productDataSet) {
            list($fixtureClass, $dataSet) = explode('::', $productDataSet);
            /** @var FixtureInterface[] $products */
            $products[$key] = $this->fixtureFactory->createByCode(
                trim($fixtureClass),
                ['dataSet' => trim($dataSet)]
            );
            $products[$key]->persist();
        }

        return ['products' => $products];
    }
}
