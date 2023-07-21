<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Laminas\I18n\Validator\Alnum as LaminasAlnum;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Alphanumerical test validator
 */
class Alnum extends LaminasAlnum implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID      => "Invalid type given. String, integer or float expected",
        self::NOT_ALNUM    => "'%value%' contains characters which are non alphabetic and no digits",
        self::STRING_EMPTY => "'%value%' is an empty string"
    ];
}
