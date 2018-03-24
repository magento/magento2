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

use Zend\Uri\UriFactory;

class Validator extends \Zend_Validate_Abstract
{
    /**#@+
     * Error keys
     */
    const INVALID_URL = 'invalidUrl';
    /**#@-*/

    /**
     * Object constructor
     */
    public function __construct()
    {
        // set translated message template
        $this->setMessage((string)new \Magento\Framework\Phrase("Invalid URL '%value%'."), self::INVALID_URL);
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
        $this->_setValue($value);

        try {
            $uri = UriFactory::factory($value);
            if ($uri->isValid()) {
                return true;
            }
        } catch (Exception $e) {/** left empty */}

        $this->_error(self::INVALID_URL);
        return false;
    }
}
