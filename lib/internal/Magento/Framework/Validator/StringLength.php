<?php
/**
 * String length validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use Laminas\Validator\StringLength as LaminasStringLength;

class StringLength extends LaminasStringLength
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
