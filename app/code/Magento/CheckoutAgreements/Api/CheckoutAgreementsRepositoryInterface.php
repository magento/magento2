<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Api;

interface CheckoutAgreementsRepositoryInterface
{
    /**
     * Lists active checkout agreements.
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[]
     */
    public function getList();
}
