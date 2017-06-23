<?php
/**
 * Email address validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Zend_Config;

class EmailAddress extends \Zend_Validate_EmailAddress implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * Instantiates email validator for local use
     *
     * The following option keys are supported:
     * 'hostname' => A hostname validator, see Zend_Validate_Hostname
     * 'allow'    => Options for the hostname validator, see Zend_Validate_Hostname::ALLOW_*
     * 'mx'       => If MX check should be enabled, boolean
     * 'deep'     => If a deep MX check should be done, boolean
     * 'tld'      => If TLD validation should be done, boolean, default false
     *
     * @param array $options OPTIONAL
     */
    public function __construct($options = [])
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } elseif (!is_array($options)) {
            $options = func_get_args();
            $temp['allow'] = array_shift($options);
            if (!empty($options)) {
                $temp['mx'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['hostname'] = array_shift($options);
            }

            if (!empty($options)) {
                $temp['tld'] = array_shift($options);
            }

            $options = $temp;
        }
        parent::__construct($options);

        $this->getHostnameValidator()->setValidateTld(array_key_exists('tld', $options) ? $options['tld'] : false);
    }
}
