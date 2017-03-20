<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of nodes that represent numeric or associative arrays
 */
class ArrayNodeConfig
{
    /**
     * @var NodePathMatcher
     */
    private $nodePathMatcher;

    /**
     * Format: array('/associative/array/path' => '<array_key_attribute>', ...)
     *
     * @var array
     */
    private $assocArrays = [];

    /**
     * Format: array('/numeric/array/path', ...)
     *
     * @var array
     */
    private $numericArrays = [];

    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $assocArrayAttributes
     * @param array $numericArrays
     */
    public function __construct(
        NodePathMatcher $nodePathMatcher,
        array $assocArrayAttributes,
        array $numericArrays = []
    ) {
        $this->nodePathMatcher = $nodePathMatcher;
        $this->assocArrays = $assocArrayAttributes;
        $this->numericArrays = $numericArrays;
    }

    /**
     * Whether a node is a numeric array or not
     *
     * @param string $nodeXpath
     * @return bool
     */
    public function isNumericArray($nodeXpath)
    {
        foreach ($this->numericArrays as $pathPattern) {
            if ($this->nodePathMatcher->match($pathPattern, $nodeXpath)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Retrieve name of array key attribute, if a node is an associative array
     *
     * @param string $nodeXpath
     * @return string|null
     */
    public function getAssocArrayKeyAttribute($nodeXpath)
    {
        foreach ($this->assocArrays as $pathPattern => $keyAttribute) {
            if ($this->nodePathMatcher->match($pathPattern, $nodeXpath)) {
                return $keyAttribute;
            }
        }
        return null;
    }
}
