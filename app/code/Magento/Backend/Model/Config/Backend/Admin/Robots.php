<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config backend model for robots.txt
 */
namespace Magento\Backend\Model\Config\Backend\Admin;

use Magento\Framework\App\Filesystem\DirectoryList;

class Robots extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;

    /**
     * @var string
     */
    protected $_file;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $config, $resource, $resourceCollection, $data);
        $this->_directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->_file = 'robots.txt';
    }

    /**
     * Return content of default robot.txt
     *
     * @return bool|string
     */
    protected function _getDefaultValue()
    {
        if ($this->_directory->isFile($this->_file)) {
            return $this->_directory->readFile($this->_file);
        }
        return false;
    }

    /**
     * Load default content from robots.txt if customer does not define own
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        if (!(string)$this->getValue()) {
            $this->setValue($this->_getDefaultValue());
        }

        return parent::_afterLoad();
    }

    /**
     * Check and process robots file
     *
     * @return $this
     */
    public function afterSave()
    {
        if ($this->getValue()) {
            $this->_directory->writeFile($this->_file, $this->getValue());
        }

        return parent::afterSave();
    }
}
