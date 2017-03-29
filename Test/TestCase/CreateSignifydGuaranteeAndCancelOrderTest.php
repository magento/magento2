<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\TestCase;

use Magento\Mtf\TestCase\Scenario;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\Adminhtml\OrderIndex;
use Magento\Sales\Test\Page\Adminhtml\SalesOrderView;
use Magento\Sales\Test\TestStep\CancelOrderStep;
use Magento\Signifyd\Test\Page\SignifydConsole\SignifydNotifications;

/**
 * Preconditions:
 * 1. Configure shipping method.
 * 2. Configure payment method.
 * 3. Configure Signifyd fraud protection tool
 * 4. Create products.
 * 5. Create and setup customer.
 *
 * Steps:
 * 1. Log in to Signifyd account.
 * 2. Remove all existing webhooks by test team.
 * 3. Add new webhook set.
 * 4. Log in Storefront.
 * 5. Add products to the Shopping Cart.
 * 6. Click the 'Proceed to Checkout' button.
 * 7. Fill shipping information.
 * 8. Select shipping method.
 * 9. Select payment method.
 * 10. Specify credit card data.
 * 11. Click 'Place order' button.
 * 12. Search for created case.
 * 13. Open created case.
 * 14. Click "Flag case as good" button.
 * 15. Perform case info assertions.
 * 16. Log in to Admin.
 * 17. Proceed to order grid.
 * 18. Perform Signifyd guarantee status assertions.
 * 19. Proceed to order view.
 * 20. Perform order status and case info assertions.
 * 21. Click Cancel button.
 * 22. Perform remaining assertions.
 *
 * @group Signifyd
 * @ZephyrId MAGETWO-62120, MAGETWO-63221
 */
class CreateSignifydGuaranteeAndCancelOrderTest extends Scenario
{
    /* tags */
    const MVP = 'yes';
    const TEST_TYPE = '3rd_party_test';
    const SEVERITY = 'S1';
    /* end tags */

    /**
     * Order page.
     *
     * @var OrderIndex
     */
    private $orderIndex;

    /**
     * Sales order view page.
     *
     * @var SalesOrderView
     */
    private $salesOrderView;

    /**
     * Cancel order test step.
     *
     * @var CancelOrderStep
     */
    private $cancelOrderStep;

    /**
     * Order fixture.
     *
     * @var OrderInjectable
     */
    private $orderInjectable;

    /**
     * Signifyd notifications page.
     *
     * @var SignifydNotifications
     */
    private $signifydNotifications;

    /**
     * Array of Signifyd config data.
     *
     * @var array
     */
    private $signifydData;

    /**
     * @param OrderIndex $orderIndex
     * @param SalesOrderView $salesOrderView
     * @param CancelOrderStep $cancelOrderStep
     * @param OrderInjectable $orderInjectable
     * @param SignifydNotifications $signifydNotifications
     */
    public function __inject(
        OrderIndex $orderIndex,
        SalesOrderView $salesOrderView,
        CancelOrderStep $cancelOrderStep,
        OrderInjectable $orderInjectable,
        SignifydNotifications $signifydNotifications
    ) {
        $this->orderIndex = $orderIndex;
        $this->salesOrderView = $salesOrderView;
        $this->cancelOrderStep = $cancelOrderStep;
        $this->orderInjectable = $orderInjectable;
        $this->signifydNotifications = $signifydNotifications;
    }

    /**
     * Runs one page checkout test.
     *
     * @param array $signifydData
     * @return void
     */
    public function test(array $signifydData)
    {
        $this->signifydData = $signifydData;

        $this->executeScenario();
    }

    /**
     * Tear down for scenario variations.
     *
     * Signifyd needs this cleanup for guarantee decline. If we had have many cases
     * with approved guarantees, and same order id, Signifyd will not create
     * guarantee approve status for new cases.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->orderIndex->open();
        $this->orderIndex->getSalesOrderGrid()
            ->searchAndOpen(['id' => $this->orderInjectable->getId()]);
        if ($this->salesOrderView->getOrderInfoBlock()->getOrderStatus() !== 'Canceled') {
            $this->cancelOrderStep->run();
        }

        if ($this->signifydData['cleanupWebhooks']) {
            $this->signifydNotifications->open();
            $this->signifydNotifications->getWebhooksBlock()
                ->cleanup($this->signifydData['team']);
        }
    }
}
