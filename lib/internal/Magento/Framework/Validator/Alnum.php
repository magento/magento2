<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\I18n\Validator\Alnum as LaminasAlnum;

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
