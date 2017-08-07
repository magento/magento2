<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block\Customer;

use Magento\Framework\View\Element\Template;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Block\TokenRendererInterface;
use Magento\Vault\Model\CustomerTokenManagement;

/**
 * Class PaymentTokens
 * @since 2.1.3
 */
abstract class PaymentTokens extends Template
{
    /**
     * @var PaymentTokenInterface[]
     * @since 2.1.3
     */
    private $customerTokens;

    /**
     * @var CustomerTokenManagement
     * @since 2.1.3
     */
    private $customerTokenManagement;

    /**
     * PaymentTokens constructor.
     * @param Template\Context $context
     * @param CustomerTokenManagement $customerTokenManagement
     * @param array $data
     * @since 2.1.3
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
     * @since 2.1.3
     */
    abstract public function getType();

    /**
     * @return PaymentTokenInterface[]
     * @since 2.1.3
     */
    public function getPaymentTokens()
    {
        $tokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($this->getCustomerTokens() as $token) {
            if ($token->getType() === $this->getType()) {
                $tokens[] = $token;
            }
        }
        return $tokens;
    }

    /**
     * @param PaymentTokenInterface $token
     * @return string
     * @since 2.1.3
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
     * @since 2.1.3
     */
    public function isExistsCustomerTokens()
    {
        return !empty($this->getCustomerTokens());
    }

    /**
     * Get customer session tokens
     * @return PaymentTokenInterface[]
     * @since 2.1.3
     */
    private function getCustomerTokens()
    {
        if (empty($this->customerTokens)) {
            $this->customerTokens = $this->customerTokenManagement->getCustomerSessionTokens();
        }
        return $this->customerTokens;
    }
}
