<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url;

/**
 * Validate route and paths
 */
class RouteValidator extends \Zend_Validate_Abstract
{
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
        $this->setMessage((string)new \Magento\Framework\Phrase("Invalid URL '%value%'."), Validator::INVALID_URL);
        $this->validator = $validator;
    }

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = [Validator::INVALID_URL => "Invalid URL '%value%'."];

    /**
     * Validate route/path
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->validator->setAllowRelative(true);
        $this->validator->setAllowAbsolute(false);
        $this->_setValue($value);

        $valid = $this->validator->isValid($value)
            && $this->validator->getUriHandler()->getQuery() === null
            //prevent directory traversal
            && strpos($value, '..') === false;

        if (!$valid) {
            $this->_error(Validator::INVALID_URL);
        }

        return $valid;
    }
}
