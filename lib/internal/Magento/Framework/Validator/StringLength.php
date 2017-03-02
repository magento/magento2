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
            $orig = ini_get('default_charset');
            ini_set('default_charset', $encoding);
            if (!ini_get('default_charset')) {
                throw new \Zend_Validate_Exception('Given encoding not supported on this OS!');
            }
            ini_set('default_charset', $orig);
        }

        $this->_encoding = $encoding;
        return $this;
    }
}
