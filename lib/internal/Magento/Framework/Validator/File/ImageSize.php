<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\File;

use Laminas\Validator\File\ImageSize as LaminasImageSize;
use Magento\Framework\Validator\ValidatorInterface;

class ImageSize extends LaminasImageSize implements ValidatorInterface
{
}
