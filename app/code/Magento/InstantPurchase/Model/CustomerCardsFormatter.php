<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\InstantPurchase\Model;

use Magento\Customer\Model\Customer;

class CustomerCardsFormatter
{
    public static $baseCardTypes = [
        'AE' => 'American Express',
        'VI' => 'Visa',
        'MC' => 'MasterCard',
        'DI' => 'Discover',
        'JBC' => 'JBC',
        'CUP' => 'China Union Pay',
        'MI' => 'Maestro',
    ];

    /**
     * @var CustomerCreditCardManager
     */
    private $customerCreditCardManager;

    /**
     * CustomerCardsFormatter constructor.
     * @param CustomerCreditCardManager $customerCreditCardManager
     */
    public function __construct(
        CustomerCreditCardManager $customerCreditCardManager
    ) {
        $this->customerCreditCardManager = $customerCreditCardManager;
    }

    /**
     * @param Customer $customer
     * @return array
     */
    public function getFormattedCards(Customer $customer): array
    {
        $cardsFormatted = [];
        $customerCc = $this->customerCreditCardManager->getVisibleAvailableTokens($customer->getId());
        foreach ($customerCc as $cc) {
            $cardsFormatted[] = [
                'card' => $this->formatCc($cc),
                'id' => $cc->getEntityId()
            ];
        }

        return $cardsFormatted;
    }

    /**
     * @param \Magento\Vault\Api\Data\PaymentTokenInterface $cc
     * @return string
     */
    private function formatCc(\Magento\Vault\Api\Data\PaymentTokenInterface $cc): string
    {
        $details = json_decode($cc->getTokenDetails(), true);
        return sprintf(
            '%s: %s, %s: %s (%s: %s)',
            _('Type'),
            self::$baseCardTypes[$details['type']],
            __('ending'),
            $details['maskedCC'],
            _('expires'),
            $details['expirationDate']
        );
    }
}
