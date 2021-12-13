<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Quote\Model\Cart\Data;

/**
 * DTO for quote item entered option
 */
class EnteredOption
{
    /**
     * @var string
     */
    private $uid;

    /**
     * @var string
     */
    private $value;

    /**
     * @param string $uid
     * @param string $value
     */
    public function __construct(string $uid, string $value)
    {
        $this->uid = $uid;
        $this->value = $value;
    }

    /**
     * Returns entered option ID
     *
     * @return string
     */
    public function getUid(): string
    {
        return $this->uid;
    }

    /**
     * Returns entered option value
     *
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
