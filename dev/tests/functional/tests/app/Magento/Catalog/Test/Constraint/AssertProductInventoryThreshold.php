<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Test\Constraint;

use Magento\Catalog\Test\Page\Product\CatalogProductView;
use Magento\Mtf\Client\BrowserInterface;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\Fixture\FixtureFactory;

/**
 * Assert that checks if threshold message setting is working correctly.
 */
class AssertProductInventoryThreshold extends AbstractConstraint
{
    /**
     * Product inventory threshold message.
     */
    const SUCCESS_MESSAGE = 'Only %s left';

    /**
     * Check if threshold message setting is working correctly.
     *
     * @param BrowserInterface $browser
     * @param FixtureInterface $product
     * @param FixtureFactory $fixtureFactory
     * @param CatalogProductView $catalogProductView
     * @param array $threshold
     * @return void
     */
    public function processAssert(
        BrowserInterface $browser,
        FixtureInterface $product,
        FixtureFactory $fixtureFactory,
        CatalogProductView $catalogProductView,
        array $threshold
    ) {
        foreach ($threshold as $thresholdItem) {
            $this->buyProduct($fixtureFactory, $product, $thresholdItem['qty']);
            $browser->open($_ENV['app_frontend_url'] . $product->getUrlKey() . '.html');
            $isThresholdMessageDisplayed = $catalogProductView->getViewBlock()
                ->isThresholdMessageDisplayed($browser, $product);
            \PHPUnit_Framework_Assert::assertEquals(
                $isThresholdMessageDisplayed,
                $thresholdItem['is_message_displayed'],
                'Product inventory threshold message display is not correct.'
            );
            if ($thresholdItem['is_message_displayed']) {
                \PHPUnit_Framework_Assert::assertEquals(
                    sprintf(self::SUCCESS_MESSAGE, $thresholdItem['expected']),
                    $catalogProductView->getViewBlock()->getThresholdMessage(),
                    'Product inventory success message is not displayed.'
                );
            }
        }
    }

    /**
     * Creates an order with product.
     *
     * @param FixtureFactory $fixtureFactory
     * @param FixtureInterface $product
     * @param int $qty
     * @return void
     */
    private function buyProduct(FixtureFactory $fixtureFactory, FixtureInterface $product, $qty)
    {
        $newProduct = $fixtureFactory->createByCode(
            'catalogProductSimple',
            ['data' => ['sku' => $product->getSku(), 'checkout_data' => ['qty' => $qty]]]
        );
        $order = $fixtureFactory->createByCode('orderInjectable', [
            'dataset' => 'guest',
            'data' => [
                'entity_id' => [
                    'products' => [$newProduct]
                ]
            ]
        ]);
        $order->persist();
    }

    /**
     * Returns a string representation of the object.
     *
     * @return string
     */
    public function toString()
    {
        return 'Product inventory threshold message display is correct.';
    }
}
