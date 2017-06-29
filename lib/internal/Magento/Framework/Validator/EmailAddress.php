<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

class EmailAddress extends \Zend_Validate_EmailAddress implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * Instantiates hostname validator for local use.
     * TLD validation is off by default.
     *
     * The following option keys are supported:
     * 'hostname' => A hostname validator, see Zend_Validate_Hostname
     * 'allow'    => Options for the hostname validator, see Zend_Validate_Hostname::ALLOW_*
     * 'mx'       => If MX check should be enabled, boolean
     * 'deep'     => If a deep MX check should be done, boolean
     *
     * @param array|string|Zend_Config $options OPTIONAL
     */
    public function __construct($options = [])
    {
        parent::__construct($options);

        $this->getHostnameValidator()->setValidateTld(false);
    }

    /**
     * Sets whether or not top-level domains should be validated
     *
     * @param bool $shouldValidate
     * @return void
     */
    public function setValidateTld(bool $shouldValidate)
    {
        $this->getHostnameValidator()->setValidateTld($shouldValidate);
    }
}
