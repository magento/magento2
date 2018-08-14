<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\TestStep;

use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Page\CustomerAccountLogout;
use Magento\Sales\Test\Fixture\OrderInjectable;
use Magento\Sales\Test\Page\SalesGuestForm;
use Magento\Mtf\TestStep\TestStepInterface;

/**
 * Open sales order page on frontend for guest.
 */
class OpenSalesOrderOnFrontendForGuestStep implements TestStepInterface
{
    /**
     * Customer log out page.
     *
     * @var CustomerAccountLogout
     */
    protected $customerAccountLogout;

    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Sales guest page.
     *
     * @var SalesGuestForm
     */
    protected $salesGuestForm;

    /**
     * Fixture order.
     *
     * @var OrderInjectable
     */
    protected $order;

    /**
     * @constructor
     * @param CustomerAccountLogout $customerAccountLogout
     * @param CmsIndex $cmsIndex
     * @param SalesGuestForm $salesGuestForm
     * @param OrderInjectable $order
     */
    public function __construct(
        CustomerAccountLogout $customerAccountLogout,
        CmsIndex $cmsIndex,
        SalesGuestForm $salesGuestForm,
        OrderInjectable $order
    ) {
        $this->customerAccountLogout = $customerAccountLogout;
        $this->cmsIndex = $cmsIndex;
        $this->salesGuestForm = $salesGuestForm;
        $this->order = $order;
    }

    /**
     * Run step.
     *
     * @return void
     */
    public function run()
    {
        $this->customerAccountLogout->open();
        $this->cmsIndex->getFooterBlock()->clickLink('Orders and Returns');
        $this->salesGuestForm->getSearchForm()->fill($this->order);
        $this->salesGuestForm->getSearchForm()->submit();
    }
}
