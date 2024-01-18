<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Laminas\Validator\StringLength as LaminasStringLength;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * String length test validator
 */
class StringLength extends LaminasStringLength implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID   => "Invalid type given. String expected",
        self::TOO_SHORT => "'%value%' is less than %min% characters long",
        self::TOO_LONG  => "'%value%' is more than %max% characters long",
    ];
}
