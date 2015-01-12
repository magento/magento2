<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Template model class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractTemplate extends AbstractModel implements TemplateTypesInterface
{
    /**
     * Default design area for emulation
     */
    const DEFAULT_DESIGN_AREA = 'frontend';

    /**
     * Configuration of design package for template
     *
     * @var \Magento\Framework\Object
     */
    protected $_designConfig;

    /**
     * Configuration of emulated design package.
     *
     * @var \Magento\Framework\Object|boolean
     */
    protected $_emulatedDesignConfig = false;

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Store id
     *
     * @var int
     */
    protected $_store;

    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * @var \Magento\Core\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_design = $design;
        $this->_area = isset($data['area']) ? $data['area'] : null;
        $this->_store = isset($data['store']) ? $data['store'] : null;
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * Applying of design config
     *
     * @return $this
     */
    protected function _applyDesignConfig()
    {
        $designConfig = $this->getDesignConfig();
        $store = $designConfig->getStore();
        $storeId = is_object($store) ? $store->getId() : $store;
        $area = $designConfig->getArea();
        if (!is_null($storeId)) {
            $this->_appEmulation->startEnvironmentEmulation($storeId, $area);
        }
        return $this;
    }

    /**
     * Revert design settings to previous
     *
     * @return $this
     */
    protected function _cancelDesignConfig()
    {
        $this->_appEmulation->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * Get design configuration data
     *
     * @return \Magento\Framework\Object
     */
    public function getDesignConfig()
    {
        if ($this->_designConfig === null) {
            if ($this->_area === null) {
                $this->_area = $this->_design->getArea();
            }
            if ($this->_store === null) {
                $this->_store = $this->_storeManager->getStore()->getId();
            }
            $this->_designConfig = new \Magento\Framework\Object(
                ['area' => $this->_area, 'store' => $this->_store]
            );
        }
        return $this->_designConfig;
    }

    /**
     * Initialize design information for template processing
     *
     * @param array $config
     * @return $this
     * @throws \Magento\Framework\Exception
     */
    public function setDesignConfig(array $config)
    {
        if (!isset($config['area']) || !isset($config['store'])) {
            throw new \Magento\Framework\Exception('Design config must have area and store.');
        }
        $this->getDesignConfig()->setData($config);
        return $this;
    }

    /**
     * Save current design config and replace with design config from specified store
     * Event is not dispatched.
     *
     * @param int|string $storeId
     * @param string $area
     * @return void
     */
    public function emulateDesign($storeId, $area = self::DEFAULT_DESIGN_AREA)
    {
        if ($storeId) {
            // save current design settings
            $this->_emulatedDesignConfig = clone $this->getDesignConfig();
            if ($this->getDesignConfig()->getStore() != $storeId) {
                $this->setDesignConfig(['area' => $area, 'store' => $storeId]);
                $this->_applyDesignConfig();
            }
        } else {
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Revert to last design config, used before emulation
     *
     * @return void
     */
    public function revertDesign()
    {
        if ($this->_emulatedDesignConfig) {
            $this->setDesignConfig($this->_emulatedDesignConfig->getData());
            $this->_cancelDesignConfig();
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function isPlain()
    {
        return $this->getType() == self::TYPE_TEXT;
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    abstract public function getType();
}
