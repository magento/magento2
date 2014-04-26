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

namespace Magento\Newsletter\Model\Resource\Problem;

use Magento\TestFramework\Helper\Bootstrap;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Newsletter\Model\Resource\Problem\Collection
     */
    protected $_collection;

    protected function setUp()
    {
        $this->_collection = Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Resource\Problem\Collection');
    }

    /**
     * @magentoDataFixture Magento/Newsletter/_files/problems.php
     */
    public function testAddCustomersData()
    {
        /** @var \Magento\Customer\Service\V1\CustomerAccountServiceInterface $customerAccountService */
        $customerAccountService = Bootstrap::getObjectManager()
            ->create('Magento\Customer\Service\V1\CustomerAccountServiceInterface');
        $customer = $customerAccountService->getCustomerDetails(1)->getCustomer();
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Subscriber')->loadByEmail($customer->getEmail());
        /** @var \Magento\Newsletter\Model\Problem $problem */
        $problem = Bootstrap::getObjectManager()
            ->create('Magento\Newsletter\Model\Problem')->addSubscriberData($subscriber);

        $item = $this->_collection->addSubscriberInfo()->load()->getFirstItem();

        $this->assertEquals($problem->getProblemErrorCode(), $item->getErrorCode());
        $this->assertEquals($problem->getProblemErrorText(), $item->getErrorText());
        $this->assertEquals($problem->getSubscriberId(), $item->getSubscriberId());
        $this->assertEquals($customer->getEmail(), $item->getSubscriberEmail());
        $this->assertEquals($customer->getFirstname(), $item->getCustomerFirstName());
        $this->assertEquals($customer->getLastname(), $item->getCustomerLastName());
        $this->assertContains($customer->getFirstname(), $item->getCustomerName());
    }

}
