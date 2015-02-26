<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Stub system config form block for integration test
 */
namespace Magento\Config\Block\System\Config;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 */
class FormStub extends \Magento\Config\Block\System\Config\Form
{
    /**
     * @var array
     */
    protected $_configDataStub = [];

    /**
     * @var array
     */
    protected $_configRootStub = [];

    /**
     * Sets stub config data
     *
     * @param array $configData
     */
    public function setStubConfigData(array $configData = [])
    {
        $this->_configDataStub = $configData;
    }

    /**
     * Sets stub config root
     *
     * @param array $configRoot
     * @return void
     */
    public function setStubConfigRoot(array $configRoot = [])
    {
        $this->_configRootStub = $configRoot;
    }

    /**
     * Initialize properties of object required for test.
     *
     * @return \Magento\Config\Block\System\Config\Form
     */
    protected function _initObjects()
    {
        parent::_initObjects();
        $this->_configData = $this->_configDataStub;
        if ($this->_configRootStub) {
            $this->_configRoot = $this->_configRootStub;
        }
        $this->_fieldRenderer = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Config\Block\System\Config\Form\Field'
        );
    }
}
