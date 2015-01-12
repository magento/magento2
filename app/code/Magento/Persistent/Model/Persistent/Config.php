<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Persistent;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Persistent Config Model
 */
class Config
{
    /**
     * Path to config file
     *
     * @var string
     */
    protected $_configFilePath;

    /**
     * @var \Magento\Framework\Config\DomFactory
     */
    protected $_domFactory;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $_moduleReader;

    /**
     * @var \DOMXPath
     */
    protected $_configDomXPath = null;

    /**
     * Layout model
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * App state model
     *
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * Model factory
     *
     * @var \Magento\Persistent\Model\Factory
     */
    protected $_persistentFactory;

    /**
     * Filesystem
     *
     * @var \Magento\Framework\Filesystem\Directory\Read;
     */
    protected $_modulesDirectory;

    /**
     * @param \Magento\Framework\Config\DomFactory $domFactory
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Persistent\Model\Factory $persistentFactory
     * @param \Magento\Framework\Filesystem $filesystem
     */
    public function __construct(
        \Magento\Framework\Config\DomFactory $domFactory,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\State $appState,
        \Magento\Persistent\Model\Factory $persistentFactory,
        \Magento\Framework\Filesystem $filesystem
    ) {
        $this->_domFactory = $domFactory;
        $this->_moduleReader = $moduleReader;
        $this->_layout = $layout;
        $this->_appState = $appState;
        $this->_persistentFactory = $persistentFactory;
        $this->_modulesDirectory = $filesystem->getDirectoryRead(DirectoryList::MODULES);
    }

    /**
     * Set path to config file that should be loaded
     *
     * @param string $path
     * @return $this
     */
    public function setConfigFilePath($path)
    {
        $this->_configFilePath = $path;
        return $this;
    }

    /**
     * Get persistent XML config xpath
     *
     * @return \DOMXPath
     * @throws \Magento\Framework\Model\Exception
     */
    protected function _getConfigDomXPath()
    {
        if (is_null($this->_configDomXPath)) {
            $filePath = $this->_modulesDirectory->getRelativePath($this->_configFilePath);
            $isFile = $this->_modulesDirectory->isFile($filePath);
            $isReadable = $this->_modulesDirectory->isReadable($filePath);
            if (!$isFile || !$isReadable) {
                throw new \Magento\Framework\Model\Exception(
                    __('We cannot load the configuration from file %1.', $this->_configFilePath)
                );
            }
            $xml = $this->_modulesDirectory->readFile($filePath);
            /** @var \Magento\Framework\Config\Dom $configDom */
            $configDom = $this->_domFactory->createDom(
                [
                    'xml' => $xml,
                    'idAttributes' => ['config/instances/blocks/reference' => 'id'],
                    'schemaFile' => $this->_moduleReader->getModuleDir('etc', 'Magento_Persistent') . '/persistent.xsd',
                ]
            );
            $this->_configDomXPath = new \DOMXPath($configDom->getDom());
        }
        return $this->_configDomXPath;
    }

    /**
     * Get block's persistent config info.
     *
     * @param string $block
     * @return array
     */
    public function getBlockConfigInfo($block)
    {
        $xPath = '//instances/blocks/*[block_type="' . $block . '"]';
        $blocks = $this->_getConfigDomXPath()->query($xPath);
        return $this->_convertBlocksToArray($blocks);
    }

    /**
     * Retrieve instances that should be emulated by persistent data
     *
     * @return array
     */
    public function collectInstancesToEmulate()
    {
        $xPath = '/config/instances/blocks/reference';
        $blocks = $this->_getConfigDomXPath()->query($xPath);
        $blocksArray = $this->_convertBlocksToArray($blocks);
        return ['blocks' => $blocksArray];
    }

    /**
     * Convert Blocks
     *
     * @param /DomNodeList $blocks
     * @return array
     */
    protected function _convertBlocksToArray($blocks)
    {
        $blocksArray = [];
        foreach ($blocks as $reference) {
            $referenceAttributes = $reference->attributes;
            $id = $referenceAttributes->getNamedItem('id')->nodeValue;
            $blocksArray[$id] = [];
            /** @var $referenceSubNode /DOMNode */
            foreach ($reference->childNodes as $referenceSubNode) {
                switch ($referenceSubNode->nodeName) {
                    case 'name_in_layout':
                    case 'class':
                    case 'method':
                    case 'block_type':
                        $blocksArray[$id][$referenceSubNode->nodeName] = $referenceSubNode->nodeValue;
                        break;
                    default:
                }
            }
        }
        return $blocksArray;
    }

    /**
     * Run all methods declared in persistent configuration
     *
     * @return $this
     */
    public function fire()
    {
        foreach ($this->collectInstancesToEmulate() as $type => $elements) {
            if (!is_array($elements)) {
                continue;
            }
            foreach ($elements as $info) {
                switch ($type) {
                    case 'blocks':
                        $this->fireOne($info, $this->_layout->getBlock($info['name_in_layout']));
                        break;
                }
            }
        }
        return $this;
    }

    /**
     * Run one method by given method info
     *
     * @param array $info
     * @param bool $instance
     * @return $this
     * @throws \Magento\Framework\Model\Exception
     */
    public function fireOne($info, $instance = false)
    {
        if (!$instance || isset(
            $info['block_type']
        ) && !$instance instanceof $info['block_type'] || !isset(
            $info['class']
        ) || !isset(
            $info['method']
        )
        ) {
            return $this;
        }
        $object = $this->_persistentFactory->create($info['class']);
        $method = $info['method'];

        if (method_exists($object, $method)) {
            $object->{$method}($instance);
        } elseif ($this->_appState->getMode() == \Magento\Framework\App\State::MODE_DEVELOPER) {
            throw new \Magento\Framework\Model\Exception(
                'Method "' . $method . '" is not defined in "' . get_class($object) . '"'
            );
        }

        return $this;
    }
}
