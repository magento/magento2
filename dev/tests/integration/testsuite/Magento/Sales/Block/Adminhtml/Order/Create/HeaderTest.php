<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
        return [
            'Customer and store' => [
                $customerIdFromFixture,
                $defaultStoreView,
                'Create New Order for John Smith in Default Store View',
            ],
            'No store' => [$customerIdFromFixture, null, 'Create New Order for John Smith'],
            'No customer' => [null, $defaultStoreView, 'Create New Order in Default Store View'],
            'No customer, no store' => [null, null, 'Create New Order for New Customer']
        ];
    }
}
