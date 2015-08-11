<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Model;

class TotalsInformationManagement implements \Magento\Checkout\Api\TotalsInformationManagementInterface
{
    /**
     * Quote repository.
     *
     * @var \Magento\Quote\Api\CartTotalRepositoryInterface
     */
    protected $cartTotalRepository;

    /**
     * @param \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
     */
    public function __construct(
        \Magento\Quote\Api\CartTotalRepositoryInterface $cartTotalRepository
    ) {
        $this->cartTotalRepository = $cartTotalRepository;
    }

    /**
     * {@inheritDoc}
     */
    public function calculate(
        $cartId,
        \Magento\Checkout\Api\Data\TotalsInformationInterface $addressInformation
    ) {
        return $this->cartTotalRepository->get($cartId);
    }
}
