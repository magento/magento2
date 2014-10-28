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

namespace Magento\Sales\Service\V1\Action;

/**
 * Test Class InvoiceEmailTest for Order Service
 */
class InvoiceEmailTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Sales\Model\Order\InvoiceRepository
     */
    protected $invoiceRepository;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceNotifier
     */
    protected $notifier;

    public function setUp()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $this->invoiceRepository = $this->getMock(
            '\Magento\Sales\Model\Order\InvoiceRepository',
            ['get'],
            [],
            '',
            false
        );
        $this->notifier = $this->getMock(
            '\Magento\Sales\Model\Order\InvoiceNotifier',
            ['notify', '__wakeup'],
            [],
            '',
            false
        );

        $this->service = $objectManager->getObject(
            'Magento\Sales\Service\V1\Action\InvoiceEmail',
            [
                'invoiceRepository' => $this->invoiceRepository,
                'notifier' => $this->notifier
            ]
        );
    }

    public function testInvoke()
    {
        $invoiceId = 1;
        $invoice = $this->getMock(
            '\Magento\Sales\Model\Order\Invoice',
            ['__wakeup', 'getEmailSent'],
            [],
            '',
            false
        );

        $this->invoiceRepository->expects($this->once())
            ->method('get')
            ->with($invoiceId)
            ->will($this->returnValue($invoice));
        $this->notifier->expects($this->any())
            ->method('notify')
            ->with($invoice)
            ->will($this->returnValue(true));

        $this->assertTrue($this->service->invoke($invoiceId));
    }
}
 