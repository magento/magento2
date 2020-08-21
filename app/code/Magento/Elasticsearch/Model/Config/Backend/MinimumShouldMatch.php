<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Elasticsearch minimum should match data model
 */
class MinimumShouldMatch extends Value
{
    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $result = parent::beforeSave();
        $this->validateValue();
        return $result;
    }

    /**
     * Validates config value
     *
     * @throws LocalizedException
     */
    public function validateValue(): void
    {
        if (strlen($this->getValue()) && !preg_match('/^((\d+<)?-?\d+%?\s?)+$/', $this->getValue())) {
            throw new LocalizedException(
                __(
                    'Value for the field "%1" was not saved because of the incorrect format.',
                    __('Minimum Terms to Match')
                )
            );
        }
    }
}
