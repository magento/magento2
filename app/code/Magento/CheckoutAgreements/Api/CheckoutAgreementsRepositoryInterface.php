<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\CheckoutAgreements\Api;

interface CheckoutAgreementsRepositoryInterface
{
    /**
     * Lists active checkout agreements.
     *
     * @return \Magento\CheckoutAgreements\Api\Data\AgreementInterface[]
     * @see \Magento\CheckoutAgreements\Service\V1\Agreement\ReadServiceInterface::getList
     */
    public function getList();
}
