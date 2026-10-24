<?php
/**
 * Copyright 2024 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Email\Model\Template\Config;

/**
 * Throw exception if email template has unexpected template id value
 */
class UnexpectedTemplateIdValueException extends \UnexpectedValueException
{
    /**
     * Exception trace
     *
     * @return string
     */
    public function __toString(): string
    {
        return preg_replace(
            "/(Stack trace:).*$/s",
            "$1" . PHP_EOL . "#0 {main}",
            parent::__toString()
        );
    }
}
