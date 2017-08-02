<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales total nodes config model
 */
namespace Magento\Sales\Model;

/**
 * Class \Magento\Sales\Model\Config
 *
 * @since 2.0.0
 */
class Config implements \Magento\Sales\Model\ConfigInterface
{
    /**
     * Modules configuration model
     *
     * @var \Magento\Sales\Model\Config\Data
     * @since 2.0.0
     */
    protected $_dataContainer;

    /**
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * @param \Magento\Sales\Model\Config\Data $dataContainer
     * @param \Magento\Framework\App\State $appState
     * @since 2.0.0
     */
    public function __construct(\Magento\Sales\Model\Config\Data $dataContainer, \Magento\Framework\App\State $appState)
    {
        $this->_dataContainer = $dataContainer;
        $this->_appState = $appState;
    }

    /**
     * Retrieve renderer for area from config
     *
     * @param string $section
     * @param string $group
     * @param string $code
     * @return array
     * @since 2.0.0
     */
    public function getTotalsRenderer($section, $group, $code)
    {
        $path = implode('/', [$section, $group, $code, 'renderers', $this->_appState->getAreaCode()]);
        return $this->_dataContainer->get($path);
    }

    /**
     * Retrieve totals for group
     * e.g. quote, etc
     *
     * @param string $section
     * @param string $group
     * @return array
     * @since 2.0.0
     */
    public function getGroupTotals($section, $group)
    {
        $path = implode('/', [$section, $group]);
        return $this->_dataContainer->get($path);
    }

    /**
     * Get available product types
     *
     * @return array
     * @since 2.0.0
     */
    public function getAvailableProductTypes()
    {
        return $this->_dataContainer->get('order/available_product_types');
    }
}
