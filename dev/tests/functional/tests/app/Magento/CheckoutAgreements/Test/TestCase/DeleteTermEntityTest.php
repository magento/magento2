<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\CheckoutAgreements\Test\TestCase;

use Magento\CheckoutAgreements\Test\Fixture\CheckoutAgreement;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementIndex;
use Magento\CheckoutAgreements\Test\Page\Adminhtml\CheckoutAgreementNew;
use Mtf\ObjectManager;
use Mtf\TestCase\Injectable;

/**
 * Test creation for DeleteTermEntityTest.
 *
 * Test Flow:
 *
 * Preconditions:
 * 1. Enable "Terms and Conditions": Stores > Configuration > Sales > Checkout > Checkout Options
 * 2. Create term according to dataSet
 *
 * Steps:
 * 1. Open Backend Stores > Terms and Conditions
 * 2. Open created Term from preconditions
 * 3. Click on 'Delete' button
 * 4. Perform all assertions
 *
 * @group Terms_and_Conditions_(CS)
 * @ZephyrId MAGETWO-29687
 */
class DeleteTermEntityTest extends Injectable
{
    /**
     * Checkout agreement index page.
     *
     * @var CheckoutAgreementIndex
     */
    protected $agreementIndex;

    /**
     * Checkout agreement new page.
     *
     * @var CheckoutAgreementNew
     */
    protected $agreementNew;

    /**
     * Inject data.
     *
     * @param CheckoutAgreementNew $agreementNew
     * @param CheckoutAgreementIndex $agreementIndex
     * @return void
     */
    public function __inject(
        CheckoutAgreementNew $agreementNew,
        CheckoutAgreementIndex $agreementIndex
    ) {
        $this->agreementNew = $agreementNew;
        $this->agreementIndex = $agreementIndex;

        // TODO: Move set up configuration to "__prepare" method after fix bug MAGETWO-29331
        $this->objectManager->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'checkout_term_condition']
        )->run();
    }

    /**
     * Delete Term Entity test.
     *
     * @param CheckoutAgreement $agreement
     * @return void
     */
    public function test(CheckoutAgreement $agreement)
    {
        // Precondition
        $agreement->persist();

        // Steps
        $this->agreementIndex->open()->getAgreementGridBlock()->searchAndOpen(['name' => $agreement->getName()]);
        $this->agreementNew->getPageActionsBlock()->delete();
    }

    // TODO: Move set default configuration to "tearDownAfterClass" method after fix bug MAGETWO-29331
    /**
     * Set default configuration.
     *
     * @return void
     */
    public function tearDown()
    {
        ObjectManager::getInstance()->create(
            'Magento\Core\Test\TestStep\SetupConfigurationStep',
            ['configData' => 'checkout_term_condition', 'rollback' => true]
        )->run();
    }
}
