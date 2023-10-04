<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\EmailAddress as LaminasEmailAddress;

class EmailAddress extends LaminasEmailAddress implements ValidatorInterface
{
    /**
     * @var string[]
     */
    protected $messageTemplates = [
        self::INVALID => "Invalid type given. String expected",
        self::INVALID_FORMAT => "'%value%' is not a valid email address in the basic format local-part@hostname",
        self::INVALID_HOSTNAME => "'%hostname%' is not a valid hostname for email address '%value%'",
        self::INVALID_MX_RECORD  => "'%hostname%' does not appear to have a valid MX record for the email address " .
            " '%value%'",
        self::INVALID_SEGMENT => "'%hostname%' is not in a routable network segment. The email address '%value%' " .
            " should not be resolved from public network",
        self::DOT_ATOM => "'%localPart%' can not be matched against dot-atom format",
        self::QUOTED_STRING => "'%localPart%' can not be matched against quoted-string format",
        self::INVALID_LOCAL_PART => "'%localPart%' is not a valid local part for email address '%value%'",
        self::LENGTH_EXCEEDED => "'%value%' exceeds the allowed length",
    ];

    /**
     * Instantiates hostname validator for local use.
     * TLD validation is off by default.
     *
     * The following option keys are supported:
     * 'hostname' => A hostname validator, see \Laminas\Validator\Hostname
     * 'allow'    => Options for the hostname validator, see \Laminas\Validator\Hostname::ALLOW_*
     * 'mx'       => If MX check should be enabled, boolean
     * 'deep'     => If a deep MX check should be done, boolean
     *
     * @inheritdoc
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->getHostnameValidator()->setOptions(['useTldCheck' => false]);
    }

    /**
     * Sets whether top-level domains should be validated
     *
     * @param bool $shouldValidate
     * @return void
     */
    public function setValidateTld(bool $shouldValidate)
    {
        $this->getHostnameValidator()->setOptions(['useTldCheck' => $shouldValidate]);
    }
}
