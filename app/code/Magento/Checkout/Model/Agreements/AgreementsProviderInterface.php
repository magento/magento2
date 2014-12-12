<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Checkout\Model\Agreements;

/**
 * Interface AgreementsProviderInterface
 */
interface AgreementsProviderInterface
{
    /**
     * Get list of Required Agreement Ids
     *
     * @return int[]
     */
    public function getRequiredAgreementIds();
}
