<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Validate URL
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Url;

class Validator extends \Zend_Validate_Abstract
{
    /**#@+
     * Error keys
     */
    const INVALID_URL = 'invalidUrl';
    /**#@-*/

    /**
     * @var \Zend\Validator\Uri
     */
    private $validator;

    /**
     * @param \Zend\Validator\Uri $validator
     */
    public function __construct(\Zend\Validator\Uri $validator)
    {
        // set translated message template
        $this->setMessage((string)new \Magento\Framework\Phrase("Invalid URL '%value%'."), self::INVALID_URL);
        $this->validator = $validator;
    }

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [self::INVALID_URL => "Invalid URL '%value%'."];

    /**
     * Validate value
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->validator->setAllowRelative(false);
        $this->validator->setAllowAbsolute(true);
        $this->_setValue($value);

        $valid = $this->validator->isValid($value);

        if (!$valid) {
            $this->_error(self::INVALID_URL);
        }

        return $valid;
    }
}
