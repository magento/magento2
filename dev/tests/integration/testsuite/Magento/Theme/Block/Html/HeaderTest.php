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

namespace Magento\Theme\Block\Html;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Theme\Block\Html\Header
 *
 * @magentoDataFixture Magento/Customer/_files/customer.php
 * @magentoAppArea frontend
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Block\Html\Header
     */
    protected $block;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $context;

    /**
     * Setup SUT
     */
    protected function setUp()
    {
        $objectManager = Bootstrap::getObjectManager();
        $this->context = $objectManager->get('Magento\Framework\App\Http\Context');
        $this->context->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, false, false);

        //Setup customer session
        $customerIdFromFixture = 1;
        $customerSession = Bootstrap::getObjectManager()->create('Magento\Customer\Model\Session');
        /**
         * @var $customerService \Magento\Customer\Service\V1\CustomerAccountServiceInterface
         */
        $customerService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customerData = $customerService->getCustomer($customerIdFromFixture);
        $customerSession->setCustomerDataObject($customerData);

        //Create block and inject customer session
        $this->block = $objectManager->create(
            'Magento\Theme\Block\Html\Header',
            ['customerSession' => $customerSession]
        );

    }

    /**
     * Test default welcome message when customer is not logged in
     */
    public function testGetWelcomeDefault()
    {
        $this->assertEquals('Default welcome msg!', $this->block->getWelcome());
    }

    /**
     * Test welcome message when customer is logged in
     */
    public function testGetWelcomeLoggedIn()
    {
        $this->context->setValue(\Magento\Customer\Helper\Data::CONTEXT_AUTH, true, false);
        $this->assertEquals('Welcome, Firstname Lastname!', $this->block->getWelcome());
    }

}
