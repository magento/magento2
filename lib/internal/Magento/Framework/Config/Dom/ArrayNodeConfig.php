<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    private $assocArrays = array();

    /**
     * Format: array('/numeric/array/path', ...)
     *
     * @var array
     */
    private $numericArrays = array();

    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $assocArrayAttributes
     * @param array $numericArrays
     */
    public function __construct(
        NodePathMatcher $nodePathMatcher,
        array $assocArrayAttributes,
        array $numericArrays = array()
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
