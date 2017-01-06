<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestCase;

use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Mtf\Fixture\FixtureFactory;
use Magento\Mtf\Fixture\FixtureInterface;
use Magento\Mtf\TestCase\Injectable;
use Magento\Mtf\TestStep\TestStepFactory;

/**
 * Preconditions:
 * 1. Enable payment method one of "Check/Money Order/Bank Transfer/Cash on Delivery/Purchase Order".
 * 2. Enable shipping method one of "Flat Rate/Free Shipping".
 * 3. Create order.
 * 4. Create Invoice.
 *
 * Steps:
 * 1. Go to Sales > Orders > find out placed order and open.
 * 2. Click 'Credit Memo' button.
 * 3. Fill data from dataset.
 * 4. On order's page click 'Refund offline' button.
 * 5. Perform all assertions.
 *
 * @group Order_Management
 * @ZephyrId MAGETWO-29116
 */
class CreateCreditMemoEntityTest extends Injectable
{
    /* tags */
    const MVP = 'yes';
    /* end tags */

    /**
     * Fixture factory.
     *
     * @var FixtureFactory
     */
    protected $fixtureFactory;

    /**
     * Skip fields for create product fixture.
     *
     * @var array
     */
    protected $skipFields = [
        'attribute_set_id',
        'website_ids',
        'checkout_data',
        'type_id',
        'price',
    ];

    /**
     * Create credit memo.
     *
     * @param TestStepFactory $stepFactory
     * @param FixtureFactory $fixtureFactory
     * @param OrderInjectable $order
     * @param array $data
     * @param string|null $configData [optional]
     * @return array
     */
    public function test(
        TestStepFactory $stepFactory,
        FixtureFactory $fixtureFactory,
        OrderInjectable $order,
        array $data,
        $configData = null
    ) {
        // Preconditions
        $this->fixtureFactory = $fixtureFactory;
        $stepFactory->create(
            \Magento\Config\Test\TestStep\SetupConfigurationStep::class,
            ['configData' => $configData]
        )->run();
        $order->persist();
        $stepFactory->create(\Magento\Sales\Test\TestStep\CreateInvoiceStep::class, ['order' => $order])->run();

        // Steps
        $createCreditMemoStep = $stepFactory->create(
            \Magento\Sales\Test\TestStep\CreateCreditMemoStep::class,
            ['order' => $order, 'data' => $data]
        );
        $result = $createCreditMemoStep->run();

        return [
            'ids' => ['creditMemoIds' => $result['creditMemoIds']],
            'customer' => $order->getDataFieldConfig('customer_id')['source']->getCustomer()
        ];
    }
}
