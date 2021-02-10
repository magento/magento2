<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Block\Adminhtml\Order\Address;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks order address edit form block
 *
 * @see \Magento\Sales\Block\Adminhtml\Order\Address\Form
 *
 * @magentoAppArea adminhtml
 */
class FormTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var Form */
    private $block;

    /** @var Registry */
    private $registry;

    /** @var OrderInterfaceFactory */
    private $orderFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->block = $this->objectManager->get(LayoutInterface::class)->createBlock(Form::class);
        $this->orderFactory = $this->objectManager->get(OrderInterfaceFactory::class);
        $this->registry = $this->objectManager->get(Registry::class);
    }

    /**
     * @return void
     */
    protected function tearDown(): void
    {
        $this->registry->unregister('order_address');

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Sales/_files/order.php
     *
     * @return void
     */
    public function testGetFormValues(): void
    {
        $this->registry->unregister('order_address');
        $order = $this->orderFactory->create()->loadByIncrementId(100000001);
        $address = $order->getShippingAddress();
        $this->registry->register('order_address', $address);
        $formValues = $this->block->getFormValues();
        $this->assertEquals($address->getData(), $formValues);
    }
}
