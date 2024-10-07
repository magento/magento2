<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Menu\Config\Menu;

use DOMElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Menu configuration files handler
 * @api
 * @since 100.0.2
 */
class Dom extends \Magento\Framework\Config\Dom
{
    /**
     * Getter for node by path
     *
     * @param string $nodePath
     * @return DOMElement|null
     * @throws LocalizedException an exception is possible if original document contains
     * multiple fixed nodes
     */
    protected function _getMatchedNode($nodePath)
    {
        if (!$nodePath || !preg_match('/^\/config(\/menu)?$/i', $nodePath)) {
            return null;
        }
        return parent::_getMatchedNode($nodePath);
    }
}
