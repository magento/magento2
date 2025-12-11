<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\Ip as LaminasIp;

class Ip extends LaminasIp implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID        => "Invalid type given. String expected",
        self::NOT_IP_ADDRESS => "'%value%' does not appear to be a valid IP address",
    ];
}
