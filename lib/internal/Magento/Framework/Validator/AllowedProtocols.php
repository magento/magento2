<?php
/**
 * Protocol validator
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

use \Zend\Uri\Uri;

/**
 * Check is URI starts from allowed protocol
 *
 * Class AllowedProtocols
 * @package Magento\Framework\Validator
 */
class AllowedProtocols extends AbstractValidator
{
    /**
     * List of supported protocols
     *
     * @var array
     */
    private $listOfProtocols = [
        'http',
        'https',
    ];

    /**
     * Constructor.
     * @param array $listOfProtocols
     */
    public function __construct($listOfProtocols = [])
    {
        if (count($listOfProtocols)) {
            $this->listOfProtocols = $listOfProtocols;
        }
    }

    /**
     * Validate URI
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $uri = new Uri($value);
        $isValid = in_array(
            strtolower($uri->getScheme()),
            $this->listOfProtocols
        );
        if (!$isValid) {
            $this->_addMessages(["Protocol isn't allowed"]);
        }
        return $isValid;
    }
}
