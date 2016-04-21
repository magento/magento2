<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Vault\Api\Data\PaymentTokenInterface;
use Magento\Vault\Model\CustomerTokenManagement;

class CreditCards extends Template
{
    /**
     * @var CustomerTokenManagement
     */
    private $customerTokenManagement;

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
        return $this->customerTokenManagement->getCustomerSessionTokens();
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
}
