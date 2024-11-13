<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\NotEmpty as LaminasNotEmpty;

class NotEmpty extends LaminasNotEmpty implements ValidatorInterface
{
}
