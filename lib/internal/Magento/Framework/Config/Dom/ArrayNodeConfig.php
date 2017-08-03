<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of nodes that represent numeric or associative arrays
 * @since 2.0.0
 */
class ArrayNodeConfig
{
    /**
     * @var NodePathMatcher
     * @since 2.0.0
     */
    private $nodePathMatcher;

    /**
     * Format: array('/associative/array/path' => '<array_key_attribute>', ...)
     *
     * @var array
     * @since 2.0.0
     */
    private $assocArrays = [];

    /**
     * Format: array('/numeric/array/path', ...)
     *
     * @var array
     * @since 2.0.0
     */
    private $numericArrays = [];

    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $assocArrayAttributes
     * @param array $numericArrays
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
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
