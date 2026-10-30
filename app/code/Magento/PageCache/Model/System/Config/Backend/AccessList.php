<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

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
     * @return $this|\Magento\Framework\Model\AbstractModel
     */
    public function beforeSave()
    {
        parent::beforeSave();

        $value = $this->getValue();

        if (is_string($value)) {
            foreach (explode(',', $value) as $item) {
                if (!preg_match('/^[\w\.\-\:]+(\/(?:[0-9]|[12][0-9]|3[0-2]))?$/', trim($item))) {
                    throw new LocalizedException(
                        new Phrase(
                            'Access List value "%1" is not valid because of item "%2".
                                  Please use only IP addresses and host names.',
                            [$value, $item]
                        )
                    );
                }
            }
        } else {
            throw new LocalizedException(
                new Phrase(
                    'Access List value "%1" is not valid. Please use only IP addresses and host names.',
                    [$value]
                )
            );
        }

        return $this;
    }
}
