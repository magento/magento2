<?php
/**
 * Page layout config model
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType;

class Config
{
    /**
     * Available page types
     *
     * @var array
     */
    protected $_pageTypes = null;

    /**
     * Data storage
     *
     * @var  \Magento\Framework\Config\DataInterface
     */
    protected $_dataStorage;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Initialize page types list
     *
     * @return $this
     */
    protected function _initPageTypes()
    {
        if ($this->_pageTypes === null) {
            $this->_pageTypes = [];
            foreach ($this->_dataStorage->get(null) as $pageTypeId => $pageTypeConfig) {
                $pageTypeConfig['label'] = (string)new \Magento\Framework\Phrase($pageTypeConfig['label']);
                $this->_pageTypes[$pageTypeId] = new \Magento\Framework\DataObject($pageTypeConfig);
            }
        }
        return $this;
    }

    /**
     * Retrieve available page types
     *
     * @return \Magento\Framework\DataObject[]
     */
    public function getPageTypes()
    {
        $this->_initPageTypes();
        return $this->_pageTypes;
    }
}
