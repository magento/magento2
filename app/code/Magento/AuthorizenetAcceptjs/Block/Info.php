<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

/**
 * Translates the labels for the info block
 *
 * @api
 * @since 100.3.0
 */
class Info extends ConfigurableInfo
{
    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     * @since 100.3.0
     */
    protected function getLabel($field): Phrase
    {
        return __($field);
    }
}
