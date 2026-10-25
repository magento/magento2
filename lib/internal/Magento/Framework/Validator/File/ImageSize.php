<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Validator\File;

use Laminas\Validator\File\ImageSize as LaminasImageSize;
use Magento\Framework\Validator\ValidatorInterface;

class ImageSize extends LaminasImageSize implements ValidatorInterface
{
}
