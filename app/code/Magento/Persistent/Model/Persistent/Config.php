<?php
/**
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
 * @category    Magento
 * @package     Magento_Persistent
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Persistent Config Model
 */
namespace Magento\Persistent\Model\Persistent;

class Config
{
    /**
     * Path to config file
     *
     * @var string
     */
    protected $_configFilePath;

    /** @var \Magento\Config\DomFactory  */
    protected $_domFactory;

    /** @var \Magento\Core\Model\Config\Modules\Reader  */
    protected $_moduleReader;

    /** @var \DOMXPath  */
    protected $_configDomXPath = null;

    /**
     * Layout model
     *
     * @var \Magento\View\LayoutInterface
     */
    protected $_layout;

    /**
     * App state model
     *
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * Model factory
     *
     * @var \Magento\Persistent\Model\Factory
     */
    protected $_persistentFactory;

    /**
     * @param \Magento\Config\DomFactory $domFactory
     * @param \Magento\Core\Model\Config\Modules\Reader $moduleReader
     * @param \Magento\View\LayoutInterface $layout
     * @param \Magento\App\State $appState
     * @param \Magento\Persistent\Model\Factory $persistentFactory
     */
    public function __construct(
        \Magento\Config\DomFactory $domFactory,
        \Magento\Core\Model\Config\Modules\Reader $moduleReader,
        \Magento\View\LayoutInterface $layout,
        \Magento\App\State $appState,
        \Magento\Persistent\Model\Factory $persistentFactory
    ) {
        $this->_domFactory = $domFactory;
        $this->_moduleReader = $moduleReader;
        $this->_layout = $layout;
        $this->_appState = $appState;
        $this->_persistentFactory = $persistentFactory;
    }

    /**
     * Set path to config file that should be loaded
     *
     * @param string $path
     * @return \Magento\Persistent\Model\Persistent\Config
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
     * @throws \Magento\Core\Exception
     */
    protected function _getConfigDomXPath()
    {
        if (is_null($this->_configDomXPath)) {
            $filePath = $this->_configFilePath;
            if (!is_file($filePath) || !is_readable($filePath)) {
                throw new \Magento\Core\Exception(__('We cannot load the configuration from file %1.', $filePath));
            }
            $xml = file_get_contents($filePath);
            /** @var \Magento\Config\Dom $configDom */
            $configDom = $this->_domFactory->createDom(
                array(
                    'xml' => $xml,
                    'idAttributes' => array(
                        'config/instances/blocks/reference' => 'id',
                    ),
                    'schemaFile' => $this->_moduleReader
                        ->getModuleDir('etc', 'Magento_Persistent') . '/persistent.xsd'
                )
            );
            $this->_configDomXPath = new \DOMXPath($configDom->getDom());
        }
        return $this->_configDomXPath;

    }

    /**
     * Get block's persistent config info.
     *
     * @param string $block
     * @return $array
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
        return array('blocks' => $blocksArray);
    }

    /**
     * Convert Blocks
     *
     * @param DomNodeList $blocks
     * @return array
     */
    protected function _convertBlocksToArray($blocks)
    {
        $blocksArray = array();
        foreach ($blocks as $reference) {
            $referenceAttributes = $reference->attributes;
            $id = $referenceAttributes->getNamedItem('id')->nodeValue;
            $blocksArray[$id] = array();
            /** @var $referenceSubNode DOMNode */
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
     * @return \Magento\Persistent\Model\Persistent\Config
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
     * @return \Magento\Persistent\Model\Persistent\Config
     * @throws \Magento\Core\Exception
     */
    public function fireOne($info, $instance = false)
    {
        if (!$instance
            || (isset($info['block_type']) && !($instance instanceof $info['block_type']))
            || !isset($info['class'])
            || !isset($info['method'])
        ) {
            return $this;
        }
        $object = $this->_persistentFactory->create($info['class']);
        $method = $info['method'];

        if (method_exists($object, $method)) {
            $object->$method($instance);
        } elseif ($this->_appState->getMode() == \Magento\App\State::MODE_DEVELOPER) {
            throw new \Magento\Core\Exception('Method "' . $method.'" is not defined in "' . get_class($object) . '"');
        }

        return $this;
    }
}
