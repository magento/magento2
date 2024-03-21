<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Quote\Model\Backpressure\Config;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

/**
 * Handles backpressure limit config value
 */
class LimitValue extends Value
{
    /**
     * @inheritDoc
     *
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        if ($this->isValueChanged()) {
            $value = (int) $this->getValue();
            if ($value < 1) {
                throw new LocalizedException(__('Number above 0 is required for the limit'));
            }
        }

        return $this;
    }
}
