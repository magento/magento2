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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Stub system config form block for integration test
 */
namespace Magento\Backend\Block\System\Config;

class FormStub extends \Magento\Backend\Block\System\Config\Form
{
    /**
     * @var array
     */
    protected $_configDataStub = array();

    /**
     * @var array
     */
    protected $_configRootStub = array();

    /**
     * Sets stub config data
     *
     * @param array $configData
     */
    public function setStubConfigData(array $configData = array())
    {
        $this->_configDataStub = $configData;
    }

    /**
     * Sets stub config root
     *
     * @param array $configRoot
     * @return void
     */
    public function setStubConfigRoot(array $configRoot = array())
    {
        $this->_configRootStub = $configRoot;
    }

    /**
     * Initialize properties of object required for test.
     *
     * @return \Magento\Backend\Block\System\Config\Form
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
            'Magento\Backend\Block\System\Config\Form\Field'
        );
    }
}
