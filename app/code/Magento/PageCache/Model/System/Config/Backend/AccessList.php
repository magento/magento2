<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\System\Config\Backend;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

/**
 * Access List config field.
 */
class AccessList extends Varnish
{
    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $value = $this->getValue();
        if (!is_string($value) || !preg_match('/^[\w\s\.\-\,\:]+$/', $value)) {
            throw new LocalizedException(
                new Phrase(
                    'Access List value "%1" is not valid. '
                    .'Please use only IP addresses and host names.',
                    [$value]
                )
            );
        }
    }
}
