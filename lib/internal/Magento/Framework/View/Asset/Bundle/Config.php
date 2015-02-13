<?php

/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\Config\View;

class Config extends View implements ConfigInterface
{

    /**
     * Get excluded file list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedFiles($area)
    {
        $items = $this->getItems($area);
        return isset($items['file']) ? $items['file'] : [];
    }

    /**
     * Get excluded directory list
     *
     * @param string $area
     * @return array
     */
    public function getExcludedDir($area)
    {
        $items = $this->getItems($area);
        return isset($items['directory']) ? $items['directory'] : [];
    }

    /**
     * Get a list of excludes in scope of specified area
     *
     * @param string $area
     * @return array
     */
    protected function getItems($area)
    {
        return isset($this->_data[$area]) ? $this->_data[$area] : [];
    }


    /**
     * Variables are identified by area and type
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return ['/view/exclude/area' => 'name', '/view/exclude/area/item' => 'type'];
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param \DOMDocument $dom
     * @return array
     */
    protected function _extractData(\DOMDocument $dom)
    {
        $result = [];
        /** @var $excludeNode \DOMElement */
        foreach ($dom->childNodes->item(0)->childNodes as $excludeNode) {
            /** @var $areaNode \DOMElement */
            foreach ($excludeNode->getElementsByTagName('area') as $areaNode) {
                $areaName = $areaNode->getAttribute('name');
                foreach ($excludeNode->getElementsByTagName('item') as $itemNode) {
                    $itemType = $itemNode->getAttribute('type');
                    $result[$areaName][$itemType] = $itemNode->nodeValue;
                }
            }
        }
        return $result;
    }
}
