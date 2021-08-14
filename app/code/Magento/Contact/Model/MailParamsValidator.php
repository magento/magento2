<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Contact\Model;

use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

class MailParamsValidator
{
    public function validate(DataObject $mailParams): void
    {
        if (!\trim((string)$mailParams->getData('name'))) {
            throw new LocalizedException(__('Enter the Name and try again.'));
        }
        if (!\trim((string)$mailParams->getData('comment'))) {
            throw new LocalizedException(__('Enter the comment and try again.'));
        }
        if (\strpos((string)$mailParams->getData('email'), '@') === false) {
            throw new LocalizedException(__('The email address is invalid. Verify the email address and try again.'));
        }
    }
}
