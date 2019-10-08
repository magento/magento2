<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ups\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;

/**
 * Represents a config URL that may point to a UPS endpoint
 */
class UpsUrl extends Value
{
    /**
     * @inheritdoc
     */
    public function beforeSave()
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        $host = parse_url((string)$this->getValue(), \PHP_URL_HOST);

        if (!empty($host) && !preg_match('/(?:.+\.|^)ups\.com$/i', $host)) {
            // Can't add new translations in Patch releases
            throw new ValidatorException(__('Invalid request'));
        }

        return parent::beforeSave();
    }
}
