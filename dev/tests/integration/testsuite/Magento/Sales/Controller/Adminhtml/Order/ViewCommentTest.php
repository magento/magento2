<?php
/************************************************************************
 *
 * Copyright 2024 Adobe
 * All Rights Reserved.
 *
 * NOTICE: All information contained herein is, and remains
 * the property of Adobe and its suppliers, if any. The intellectual
 * and technical concepts contained herein are proprietary to Adobe
 * and its suppliers and are protected by all applicable intellectual
 * property laws, including trade secret and copyright laws.
 * Dissemination of this information or reproduction of this material
 * is strictly forbidden unless prior written permission is obtained
 * from Adobe.
 * ************************************************************************
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Adminhtml\Order;

use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\TestCase\AbstractBackendController;

class ViewCommentTest extends AbstractBackendController
{
    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->orderFactory = $this->_objectManager->get(OrderInterfaceFactory::class);
    }

    /**
     * Verify the button Label is rendered as 'Update Changes' in order comment section
     * of order details page.
     *
     * @magentoDataFixture Magento/Sales/_files/order.php
     * @magentoAppArea adminhtml
     * @return void
     * @throws LocalizedException
     */
    public function testVerifyStatusCommentUpdateButtonLabel(): void
    {
        $order = $this->orderFactory->create()->loadByIncrementId('100000001');
        $this->getRequest()->setParam('order_id', $order->getEntityId());
        $this->dispatch('backend/sales/order/view/');
        $content = $this->getResponse()->getBody();
        $this->assertStringContainsString(
            '<span>Update</span>',
            $content
        );
    }
}
