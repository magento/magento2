<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Laminas\I18n\Validator\IsInt as LaminasIsInt;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Integer test validator
 */
class IsInt extends LaminasIsInt implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID => "Invalid type given. String or integer expected",
        self::NOT_INT => "'%value%' does not appear to be an integer",
        self::NOT_INT_STRICT => 'The input is not strictly an integer'
    ];
}
