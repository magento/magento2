<?php
declare(strict_types=1);
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Adminhtml\Address;

use Magento\Framework\Phrase;

/**
 * Class to process default billing address setting
 */
class DefaultBillingAddress extends AbstractDefaultAddress
{
    /**
     * @inheritdoc
     */
    protected function setAddressAsDefault($address)
    {
        $address->setIsDefaultBilling(true);
    }

    /**
     * @inheritdoc
     */
    protected function getSuccessMessage(): Phrase
    {
        return __('Default billing address has been changed.');
    }

    /**
     * @inheritdoc
     */
    protected function getExceptionMessage(): Phrase
    {
        return __('We can\'t change default billing address right now.');
    }
}
