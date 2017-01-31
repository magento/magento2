<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\TokenRendererInterface;
use Magento\Vault\Model\CustomerTokenManagement;

/**
 * Class PaymentTokens
 */
abstract class PaymentTokens extends Template
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
     * PaymentTokens constructor.
     * @param Template\Context $context
     * @param CustomerTokenManagement $customerTokenManagement
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CustomerTokenManagement $customerTokenManagement,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->customerTokenManagement = $customerTokenManagement;
    }

    /**
     * Get type of token
     * @return string
     */
    abstract function getType();

    /**
     * @return PaymentTokenInterface[]
     */
    public function getPaymentTokens()
    {
        $tokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($this->getCustomerTokens() as $token) {
            if ($token->getType() === $this->getType()) {
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
