<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Sales\Model\ResourceModel\Order\Payment;

use Magento\Framework\Encryption\Encryptor;
use Magento\TestFramework\Helper\Bootstrap;

class EncryptionUpdateTest extends \PHPUnit\Framework\TestCase
{
    const TEST_CC_NUMBER = '4111111111111111';

    /**
     * Tests re-encryption of credit card numbers
     *
     * @magentoDataFixture Magento/Sales/_files/payment_enc_cc.php
     */
    public function testReEncryptCreditCardNumbers()
    {
        $objectManager = Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Encryption\EncryptorInterface $encyptor */
        $encyptor = $objectManager->get(\Magento\Framework\Encryption\EncryptorInterface::class);

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdate $resource */
        $resource = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Payment\EncryptionUpdate::class);
        $resource->reEncryptCreditCardNumbers();

        /** @var \Magento\Sales\Model\ResourceModel\Order\Payment\Collection $collection */
        $collection = $objectManager->create(\Magento\Sales\Model\ResourceModel\Order\Payment\Collection::class);
        $collection->addFieldToFilter('cc_number_enc', ['notnull' => true]);

        $this->assertGreaterThan(0, $collection->getTotalCount());

        /** @var \Magento\Sales\Model\Order\Payment $payment */
        foreach ($collection->getItems() as $payment) {
            $this->assertEquals(
                static::TEST_CC_NUMBER,
                $encyptor->decrypt($payment->getCcNumberEnc())
            );
            
            $this->assertStringStartsWith('0:' . Encryptor::CIPHER_LATEST . ':', $payment->getCcNumberEnc());
        }
    }
}
