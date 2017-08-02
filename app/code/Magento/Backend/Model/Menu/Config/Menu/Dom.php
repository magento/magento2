<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Config\Menu;

/**
 * Menu configuration files handler
 * @api
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected function _getMatchedNode($nodePath)
    {
        if (!preg_match('/^\/config(\/menu)?$/i', $nodePath)) {
            return null;
        }
        return parent::_getMatchedNode($nodePath);
    }
}
