<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator\Test\Unit\Test;

use Magento\Framework\Validator\ValidatorInterface;

/**
 * String length test validator
 */
class StringLength extends \Zend_Validate_StringLength implements ValidatorInterface
{
}
