<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Magento\Framework\Setup\Lists;

class Email
{
    /**
     * @var Lists
     */
    protected $lists;

    /**
     * Constructor
     *
     * @param Lists $lists
     */
    public function __construct(Lists $lists)
    {
        $this->lists = $lists;
    }

    /**
     * Validate email address contains '@' sign
     *
     * @param mixed $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;
        if (strrpos($value, '@') === false) {
            $isValid = false;
        }
        
        return $isValid;
    }
}
