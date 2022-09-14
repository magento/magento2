<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Url;

use Laminas\Validator\AbstractValidator;
use Laminas\Validator\Uri;

/**
 * URL validator
 */
class Validator extends AbstractValidator
{
    /**#@+
     * Error keys
     */
    public const INVALID_URL = 'uriInvalid';
    /**#@-*/

    /**
     * @var Uri
     */
    private $validator;

    /**
     * @param Uri $validator
     */
    public function __construct(Uri $validator)
    {
        parent::__construct();
        // set translated message template
        $this->setMessage((string)new \Magento\Framework\Phrase("Invalid URL '%value%'."), Uri::INVALID);
        $this->validator = $validator;
        $this->validator->setAllowRelative(false);
    }

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = [Uri::INVALID => "Invalid URL '%value%'."];

    /**
     * Validate value
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $this->setValue($value);

        $valid = $this->validator->isValid($value);

        if (!$valid) {
            $this->error(Uri::INVALID);
        }

        return $valid;
    }
}
