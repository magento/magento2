<?php
/**
 * String length validator
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

class StringLength extends \Zend_Validate_StringLength implements \Magento\Framework\Validator\ValidatorInterface
{
    /**
     * @var string
     */
    protected $_encoding = 'UTF-8';

    /**
     * {@inheritdoc}
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            $orig = PHP_VERSION_ID < 50600
                ? iconv_get_encoding('internal_encoding')
                : ini_get('default_charset');
            if (PHP_VERSION_ID < 50600) {
                $result = iconv_set_encoding('internal_encoding', $encoding);
            } else {
                ini_set('default_charset', $encoding);
                $result = ini_get('default_charset');
            }
            if (!$result) {
                throw new \Zend_Validate_Exception('Given encoding not supported on this OS!');
            }

            if (PHP_VERSION_ID < 50600) {
                iconv_set_encoding('internal_encoding', $orig);
            } else {
                ini_set('default_charset', $orig);
            }
        }

        $this->_encoding = $encoding;
        return $this;
    }
}
