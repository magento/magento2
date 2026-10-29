<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Laminas\Validator\NotEmpty as LaminasNotEmpty;
use Magento\Framework\Validator\ValidatorInterface;

/**
 * Not empty test validator
 */
class NotEmpty extends LaminasNotEmpty implements ValidatorInterface
{
}
