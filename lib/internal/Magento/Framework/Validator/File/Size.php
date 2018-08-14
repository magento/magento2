<?php
/**
 * Validator for the maximum size of a file up to a max of 2GB
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator\File;

class Size extends \Zend_Validate_File_Size implements \Magento\Framework\Validator\ValidatorInterface
{
}
