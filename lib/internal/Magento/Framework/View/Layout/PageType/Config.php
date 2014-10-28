<?php
/**
 * Page layout config model
 * 
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
            $this->_pageTypes = array();
            foreach ($this->_dataStorage->get(null) as $pageTypeId => $pageTypeConfig) {
                $pageTypeConfig['label'] = __($pageTypeConfig['label']);
                $this->_pageTypes[$pageTypeId] = new \Magento\Framework\Object($pageTypeConfig);
            }
        }
        return $this;
    }

    /**
     * Retrieve available page types
     *
     * @return \Magento\Framework\Object[]
     */
    public function getPageTypes()
    {
        $this->_initPageTypes();
        return $this->_pageTypes;
    }
}
