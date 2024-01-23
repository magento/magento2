<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Validator;

use Laminas\Validator\Hostname as LaminasHostname;

class Hostname extends LaminasHostname implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::CANNOT_DECODE_PUNYCODE => "'%value%' appears to be a DNS hostname but the given punycode notation" .
            " cannot be decoded",
        self::INVALID => "Invalid type given. String expected",
        self::INVALID_DASH => "'%value%' appears to be a DNS hostname but contains a dash in an invalid position",
        self::INVALID_HOSTNAME => "'%value%' does not match the expected structure for a DNS hostname",
        self::INVALID_HOSTNAME_SCHEMA => "'%value%' appears to be a DNS hostname but cannot match against hostname" .
            " schema for TLD '%tld%'",
        self::INVALID_LOCAL_NAME => "'%value%' does not appear to be a valid local network name",
        self::INVALID_URI => "'%value%' does not appear to be a valid URI hostname",
        self::IP_ADDRESS_NOT_ALLOWED => "'%value%' appears to be an IP address, but IP addresses are not allowed",
        self::LOCAL_NAME_NOT_ALLOWED => "'%value%' appears to be a local network name but local network names are " .
            "not allowed",
        self::UNDECIPHERABLE_TLD => "'%value%' appears to be a DNS hostname but cannot extract TLD part",
        self::UNKNOWN_TLD => "'%value%' appears to be a DNS hostname but cannot match TLD against known list"
    ];
}
