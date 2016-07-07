<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;

/**
 * Class PaymentTokens
 */
class PaymentTokens extends Template
{
    /**
     * @var PaymentTokenInterface[]
     */
    private $customerTokens;

    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

    /**
     * @var string
     */
    private $tokenType;

    /**
     * PaymentTokens constructor.
     * @param Template\Context $context
     * @param CustomerTokenManagement $customerTokenManagement
     * @param string $tokenType
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerTokenManagement $customerTokenManagement,
        $tokenType = PaymentTokenInterface::TOKEN_TYPE,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
        $this->tokenType = $tokenType;
    }

    /**
     * @return PaymentTokenInterface[]
     */
    public function getPaymentTokens()
    {
        $tokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($this->getCustomerTokens() as $token) {
            if ($token->getType() === $this->tokenType) {
                $tokens[] = $token;
            }
        };
        return $tokens;
    }

    /**
     * @param PaymentTokenInterface $token
     * @return string
     */
    public function renderTokenHtml(PaymentTokenInterface $token)
    {
        foreach ($this->getChildNames() as $childName) {
            $childBlock = $this->getChildBlock($childName);
            if ($childBlock instanceof TokenRendererInterface && $childBlock->canRender($token)) {
                return $childBlock->render($token);
            }
        }

        return '';
    }

    /**
     * Checks if customer tokens exists
     * @return bool
     */
    public function isExistsCustomerTokens()
    {
        return !empty($this->getCustomerTokens());
    }

    /**
     * Get customer session tokens
     * @return PaymentTokenInterface[]
     */
    private function getCustomerTokens()
    {
        if (empty($this->customerTokens)) {
            $this->customerTokens = $this->customerTokenManagement->getCustomerSessionTokens();
        }
        return $this->customerTokens;
    }
}
