<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of identifier attributes to be taken into account during merging
 * @since 2.0.0
 */
class NodeMergingConfig
{
    /**
     * @var NodePathMatcher
     * @since 2.0.0
     */
    private $nodePathMatcher;

    /**
     * Format: array('/node/path' => '<node_id_attribute>', ...)
     *
     * @var array
     * @since 2.0.0
     */
    private $idAttributes = [];

    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $idAttributes
     * @since 2.0.0
     */
    public function __construct(NodePathMatcher $nodePathMatcher, array $idAttributes)
    {
        $this->nodePathMatcher = $nodePathMatcher;
        $this->idAttributes = $idAttributes;
    }

    /**
     * Retrieve name of an identifier attribute for a node
     *
     * @param string $nodeXpath
     * @return string|null
     * @since 2.0.0
     */
    public function getIdAttribute($nodeXpath)
    {
        foreach ($this->idAttributes as $pathPattern => $idAttribute) {
            if ($this->nodePathMatcher->match($pathPattern, $nodeXpath)) {
                return $idAttribute;
            }
        }
        return null;
    }
}
