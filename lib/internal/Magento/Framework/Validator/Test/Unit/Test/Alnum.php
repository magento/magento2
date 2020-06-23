<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Magento\Framework\Validator\ValidatorInterface;

/**
 * Alphanumerical test validator
 */
class Alnum extends \Zend_Validate_Alnum implements ValidatorInterface
{
}
