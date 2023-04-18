<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\File;

use Laminas\Validator\File\IsImage as LaminasIsImage;
use Magento\Framework\Validator\ValidatorInterface;

class IsImage extends LaminasIsImage implements ValidatorInterface
{
}
