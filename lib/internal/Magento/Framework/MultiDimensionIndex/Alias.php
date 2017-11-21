<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\MultiDimensionIndex;

use Magento\Framework\Exception\LocalizedException;

/**
 * Index Alias object
 * @api
 */
class Alias
{
    /**
     * Replica index scope
     */
    const ALIAS_REPLICA = 'replica';

    /**
     * Main index scope
     */
    const ALIAS_MAIN = 'main';

    /**
     * One of self::ALIAS_*
     *
     * @var string
     */
    private $value;

    /**
     * @param string $value One of self::ALIAS_*
     * @throws LocalizedException
     */
    public function __construct(string $value)
    {
        if ($value !== self::ALIAS_REPLICA && $value !== self::ALIAS_MAIN) {
            throw new LocalizedException(__('Wrong value %value for alias', ['value' => $value]));
        }
        $this->value = $value;
    }

    /**
     * @return string One of self::ALIAS_*
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
