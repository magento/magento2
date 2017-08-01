<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Config backend model for robots.txt
 */
namespace Magento\Config\Model\Config\Backend\Admin;

use Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot;
use Magento\Framework\App\ObjectManager;

/**
 * @deprecated 2.2.0 robots.txt file is no longer stored in filesystem. It generates as response on request.
 * @since 2.0.0
 */
class Robots extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     * @since 2.0.0
     */
    protected $_directory;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $_file;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @param DocumentRoot $documentRoot
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Config\Model\Config\Reader\Source\Deployed\DocumentRoot $documentRoot = null
    ) {
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);

        $documentRoot = $documentRoot ?: ObjectManager::getInstance()->get(DocumentRoot::class);
        $this->_directory = $filesystem->getDirectoryWrite($documentRoot->getPath());
        $this->_file = 'robots.txt';
    }

    /**
     * Return content of default robot.txt
     *
     * @return bool|string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function afterSave()
    {
        if ($this->getValue()) {
            $this->_directory->writeFile($this->_file, $this->getValue());
        }

        return parent::afterSave();
    }
}
