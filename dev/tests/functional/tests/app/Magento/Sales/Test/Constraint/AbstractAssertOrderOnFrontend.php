<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Sales\Test\Constraint;

use Magento\Customer\Test\Page\CustomerAccountIndex;
use Magento\Cms\Test\Page\CmsIndex;
use Magento\Customer\Test\Fixture\CustomerInjectable;
use Mtf\Constraint\AbstractConstraint;
use Mtf\ObjectManager;

/**
 * Abstract Class AbstractAssertOrderOnFrontend
 * Abstract class for frontend asserts
 */
abstract class AbstractAssertOrderOnFrontend extends AbstractConstraint
{
    /**
     * Cms index page
     *
     * @var CmsIndex
     */
    protected $cmsIndex;

    /**
     * Customer account index page
     *
     * @var CustomerAccountIndex
     */
    protected $customerAccountIndex;

    /**
     * @constructor
     * @param ObjectManager $objectManager
     * @param CmsIndex $cmsIndex
     * @param CustomerAccountIndex $customerAccountIndex
     */
    public function __construct(
        ObjectManager $objectManager,
        CmsIndex $cmsIndex,
        CustomerAccountIndex $customerAccountIndex
    ) {
        parent::__construct($objectManager);
        $this->cmsIndex = $cmsIndex;
        $this->customerAccountIndex = $customerAccountIndex;
    }

    /**
     * Login customer and open Order page
     *
     * @param CustomerInjectable $customer
     * @return void
     */
    protected function loginCustomerAndOpenOrderPage(CustomerInjectable $customer)
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
