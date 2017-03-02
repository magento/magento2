<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Config\Dom;

/**
 * Configuration of identifier attributes to be taken into account during merging
 */
class NodeMergingConfig
{
    /**
     * @var NodePathMatcher
     */
    private $nodePathMatcher;

    /**
     * Format: array('/node/path' => '<node_id_attribute>', ...)
     *
     * @var array
     */
    private $idAttributes = [];

    /**
     * @param NodePathMatcher $nodePathMatcher
     * @param array $idAttributes
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
