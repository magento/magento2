<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;

/**
 * Class Token
 */
class Token extends CreditCards
{
    /**
     * @return PaymentTokenInterface[]
     */
    public function getPaymentTokens()
    {
        $tokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($this->customerTokenManagement->getCustomerSessionTokens() as $token) {
            if ($token->getType() === PaymentTokenInterface::TOKEN_TYPE) {
                $tokens[] = $token;
            }
        };
        return $tokens;
    }
}
