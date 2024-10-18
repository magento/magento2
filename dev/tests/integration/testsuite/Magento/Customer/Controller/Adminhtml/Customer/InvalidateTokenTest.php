<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Adminhtml\Customer;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Escaper;
use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Invalidate customer token tests.
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class InvalidateTokenTest extends AbstractBackendController
{
    /** @var Escaper */
    private $escaper;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->escaper = $this->_objectManager->get(Escaper::class);
    }

    /**
     * @magentoDataFixture Magento/Integration/_files/customer_with_integration_token.php
     *
     * @return void
     */
    public function testInvalidateCustomerToken(): void
    {
        $customerId = 1;
        $this->getRequest()->setParam('customer_id', $customerId)->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch('backend/customer/customer/invalidateToken');
        $this->assertRedirect($this->stringContains('backend/customer/index/edit/id/' . $customerId));
        $message = $this->escaper->escapeHtml('You have revoked the customer\'s tokens.');
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_SUCCESS);
    }

    /**
     * @return void
     */
    public function testInvalidateCustomerTokenWithoutParams(): void
    {
        $this->getRequest()->setMethod(HttpRequest::METHOD_GET);
        $this->dispatch('backend/customer/customer/invalidateToken');
        $this->assertRedirect($this->stringContains('backend/customer/index/index'));
        $message = $this->escaper->escapeHtml('We can\'t find a customer to revoke.');
        $this->assertSessionMessages($this->equalTo([(string)__($message)]), MessageInterface::TYPE_ERROR);
    }
}
