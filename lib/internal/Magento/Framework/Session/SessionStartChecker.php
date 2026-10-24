<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Session;

/**
 * Class to check if session can be started or not.
 */
class SessionStartChecker
{
    /**
     * @var bool
     */
    private $checkSapi;

    /**
     * @param bool $checkSapi
     */
    public function __construct(bool $checkSapi = true)
    {
        $this->checkSapi = $checkSapi;
    }

    /**
     * Can session be started or not.
     *
     * @return bool
     */
    public function check() : bool
    {
        return !($this->checkSapi && PHP_SAPI === 'cli');
    }
}
