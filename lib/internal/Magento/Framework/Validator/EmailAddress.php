<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;
use Laminas\Validator\EmailAddress as LaminasEmailValidator;

class EmailAddress extends LaminasEmailValidator
{
    public function __construct($options = [])
    {
        parent::__construct($options);
        $this->getHostnameValidator()->useTldCheck(false);
    }
}
