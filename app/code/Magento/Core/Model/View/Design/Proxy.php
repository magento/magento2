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
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Design proxy
 */
namespace Magento\Core\Model\View\Design;

class Proxy implements \Magento\View\DesignInterface
{
    /**
     * @var \Magento\ObjectManager
     */
    protected $_objectManager;

    /**
     * @var \Magento\Core\Model\View\Design
     */
    protected $_model;

    /**
     * @param \Magento\ObjectManager $objectManager
     */
    public function __construct(\Magento\ObjectManager $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @return \Magento\Core\Model\View\Design
     */
    protected function _getInstance()
    {
        if (null === $this->_model) {
            $this->_model = $this->_objectManager->get('Magento\Core\Model\View\Design');
        }
        return $this->_model;
    }

    /**
     * Set package area
     *
     * @param string $area
     * @return \Magento\View\DesignInterface
     */
    public function setArea($area)
    {
        return $this->_getInstance()->setArea($area);
    }

    /**
     * Retrieve package area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_getInstance()->getArea();
    }

    /**
     * Set theme path
     *
     * @param \Magento\Core\Model\Theme|int|string $theme
     * @param string $area
     * @return \Magento\View\DesignInterface
     */
    public function setDesignTheme($theme, $area = null)
    {
        return $this->_getInstance()->setDesignTheme($theme, $area);
    }

    /**
     * Get default theme which declared in configuration
     *
     * @param string $area
     * @param array $params
     * @return string|int
     */
    public function getConfigurationDesignTheme($area = null, array $params = array())
    {
        return $this->_getInstance()->getConfigurationDesignTheme($area, $params);
    }

    /**
     * Set default design theme
     *
     * @return \Magento\View\DesignInterface
     */
    public function setDefaultDesignTheme()
    {
        return $this->_getInstance()->setDefaultDesignTheme();
    }

    /**
     * Design theme model getter
     *
     * @return \Magento\Core\Model\Theme
     */
    public function getDesignTheme()
    {
        return $this->_getInstance()->getDesignTheme();
    }

    /**
     * Get design settings for current request
     *
     * @return array
     */
    public function getDesignParams()
    {
        return $this->_getInstance()->getDesignParams();
    }
}
