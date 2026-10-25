<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Csp\Api\Data;

/**
 * Defined Content Security Policy.
 *
 * Different policies will have different types of data but they all will have identifiers and string representations.
 *
 * @api
 */
interface PolicyInterface
{
    /**
     * Policy unique name (ID).
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Value of the policy.
     *
     * @return string
     */
    public function getValue(): string;
}
