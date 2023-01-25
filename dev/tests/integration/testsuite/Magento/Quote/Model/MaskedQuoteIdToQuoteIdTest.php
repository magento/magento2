<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model;

use Magento\TestFramework\Helper\Bootstrap as BootstrapHelper;
use Magento\Quote\Api\GuestCartManagementInterface;

class MaskedQuoteIdToQuoteIdTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * @var GuestCartManagementInterface
     */
    private $guestCartManagement;

    protected function setUp(): void
    {
        $objectManager = BootstrapHelper::getObjectManager();
        $this->maskedQuoteIdToQuoteId = $objectManager->create(MaskedQuoteIdToQuoteIdInterface::class);
        $this->guestCartManagement = $objectManager->create(GuestCartManagementInterface::class);
    }

    public function testMaskedIdToQuoteId()
    {
        $maskedQuoteId = $this->guestCartManagement->createEmptyCart();
        $quoteId = $this->maskedQuoteIdToQuoteId->execute($maskedQuoteId);

        self::assertGreaterThan(0, $quoteId);
    }

    public function testMaskedQuoteIdToQuoteIdForNonExistentQuote()
    {
        self::expectException(\Magento\Framework\Exception\NoSuchEntityException::class);

        $this->maskedQuoteIdToQuoteId->execute('test');
    }
}
