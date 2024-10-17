<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
