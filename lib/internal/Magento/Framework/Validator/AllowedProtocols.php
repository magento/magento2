<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */

namespace Magento\Framework\Validator;

use \Laminas\Uri\Uri;

/**
 * Protocol validator
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
     *
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
            strtolower($uri->getScheme() ?? ''),
            $this->listOfProtocols
        );
        if (!$isValid) {
            $this->_addMessages(["Protocol isn't allowed"]);
        }

        return $isValid;
    }
}
