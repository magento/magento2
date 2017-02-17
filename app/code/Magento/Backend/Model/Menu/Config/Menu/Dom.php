<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Config\Menu;

/**
 * Menu configuration files handler
 */
class Dom extends \Magento\Framework\Config\Dom
{
    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @return \DOMElement|null
     * @throws \Magento\Framework\Exception\LocalizedException an exception is possible if original document contains
     * multiple fixed nodes
     */
    protected function _getMatchedNode($nodePath)
    {
        if (!preg_match('/^\/config(\/menu)?$/i', $nodePath)) {
            return null;
        }
        return parent::_getMatchedNode($nodePath);
    }
}
