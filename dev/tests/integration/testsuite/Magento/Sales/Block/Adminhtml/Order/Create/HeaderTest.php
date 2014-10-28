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
namespace Magento\Sales\Block\Adminhtml\Order\Create;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * @magentoAppArea adminhtml
 */
class HeaderTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Sales\Block\Adminhtml\Order\Create\Header */
    protected $_block;

    protected function setUp()
    {
        $this->_block = Bootstrap::getObjectManager()->create('Magento\Sales\Block\Adminhtml\Order\Create\Header');
        parent::setUp();
    }

    /**
     * @param int|null $customerId
     * @param int|null $storeId
     * @param string $expectedResult
     * @magentoDataFixture Magento/Customer/_files/customer.php
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($customerId, $storeId, $expectedResult)
    {
        /** @var \Magento\Backend\Model\Session\Quote $session */
        $session = Bootstrap::getObjectManager()->get('Magento\Backend\Model\Session\Quote');
        $session->setCustomerId($customerId);
        $session->setStoreId($storeId);
        $this->assertEquals($expectedResult, $this->_block->toHtml());
    }

    public function toHtmlDataProvider()
    {
        $customerIdFromFixture = 1;
        $defaultStoreView = 1;
        return array(
            'Customer and store' => array(
                $customerIdFromFixture,
                $defaultStoreView,
                'Create New Order for Firstname Lastname in Default Store View'
            ),
            'No store' => array($customerIdFromFixture, null, 'Create New Order for Firstname Lastname'),
            'No customer' => array(null, $defaultStoreView, 'Create New Order for New Customer in Default Store View'),
            'No customer, no store' => array(null, null, 'Create New Order for New Customer')
        );
    }
}
