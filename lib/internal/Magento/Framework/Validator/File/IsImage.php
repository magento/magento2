<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Validator\File;

use Laminas\Validator\File\IsImage as LaminasIsImage;
use Magento\Framework\Validator\ValidatorInterface;

class IsImage extends LaminasIsImage implements ValidatorInterface
{
}
