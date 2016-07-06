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
 * Class CreditCards
 */
class CreditCards extends Template
{
    /**
     * @var CustomerTokenManagement
     */
    protected $customerTokenManagement;

    /**
     * @var array
     */
    private $customerTokens;

    /**
     * CreditCards constructor.
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
     * @return PaymentTokenInterface[]
     */
    public function getPaymentTokens()
    {
        $tokens = [];
        /** @var PaymentTokenInterface $token */
        foreach ($this->getCustomerTokens() as $token) {
            if ($token->getType() === PaymentTokenInterface::CARD_TYPE) {
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
