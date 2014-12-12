<?php
/**
 * Validator for the maximum size of a file up to a max of 2GB
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Validator\File;

class Size extends \Zend_Validate_File_Size implements \Magento\Framework\Validator\ValidatorInterface
{
}
