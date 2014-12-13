<?php
/**
 * String length validator
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Validator;

class StringLength extends \Zend_Validate_StringLength implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * @var string
     */
    protected $_encoding = 'UTF-8';
}
