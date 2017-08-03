<?php
/**
 * Page layout config model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\PageType;

/**
 * Class \Magento\Framework\View\Layout\PageType\Config
 *
 * @since 2.0.0
 */
class Config
{
    /**
     * Available page types
     *
     * @var array
     * @since 2.0.0
     */
    protected $_pageTypes = null;

    /**
     * Data storage
     *
     * @var  \Magento\Framework\Config\DataInterface
     * @since 2.0.0
     */
    protected $_dataStorage;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Config\DataInterface $dataStorage
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Config\DataInterface $dataStorage)
    {
        $this->_dataStorage = $dataStorage;
    }

    /**
     * Initialize page types list
     *
     * @return $this
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function getPageTypes()
    {
        $this->_initPageTypes();
        return $this->_pageTypes;
    }
}
