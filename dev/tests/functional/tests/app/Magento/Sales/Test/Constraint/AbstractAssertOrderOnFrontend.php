<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\Customer;
use Magento\Mtf\Constraint\AbstractConstraint;
use Magento\Mtf\ObjectManager;
use Magento\Mtf\System\Event\EventManagerInterface;

/**
 * Abstract class for frontend asserts.
 */
abstract class AbstractAssertOrderOnFrontend extends AbstractConstraint
{
    /**
     * Cms index page.
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer account index page.
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param EventManagerInterface $eventManager
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     */
    public function __construct(
        ObjectManager $objectManager,
        EventManagerInterface $eventManager,
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex
    ) {
        parent::__construct($objectManager, $eventManager);
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountIndex = $customerAccountIndex;
    }

    /**
     * Login customer and open Order page.
     *
     * @param Customer $customer
     * @return void
     */
    protected function loginCustomerAndOpenOrderPage(Customer $customer)
    {
        $this->cmsIndex->open();
        $loginCustomerOnFrontendStep = $this->objectManager->create(
            'Magento\Customer\Test\TestStep\LoginCustomerOnFrontendStep',
            ['customer' => $customer]
        );
        $loginCustomerOnFrontendStep->run();
        $this->cmsIndex->getLinksBlock()->openLink('My Account');
        $this->customerAccountIndex->getAccountMenuBlock()->openMenuItem('My Orders');
    }
}
