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
 * obtain it through the world-wide-web, please send an e-mail
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sales total nodes config model
 */
namespace Magento\Sales\Model;

class Config implements \Magento\Sales\Model\ConfigInterface
{
    /**
     * Modules configuration model
     *
     * @var \Magento\Sales\Model\Config\Data
     */
    protected $_dataContainer;

    /**
     * @param \Magento\Sales\Model\Config\Data $dataContainer
     */
    public function __construct(\Magento\Sales\Model\Config\Data $dataContainer)
    {
        $this->_dataContainer = $dataContainer;
    }

    /**
     * Retrieve renderer for area from config
     *
     * @param string $section
     * @param string $group
     * @param string $code
     * @param string $area
     * @return array
     */
    public function getTotalsRenderer($section, $group, $code, $area)
    {
        $path = implode('/', array($section, $group, $code, 'renderers', $area));
        return $this->_dataContainer->get($path);
    }

    /**
     * Retrieve totals for group
     * e.g. quote, nominal_totals, etc
     *
     * @param string $section
     * @param string $group
     * @return array
     */
    public function getGroupTotals($section, $group)
    {
        $path = implode('/', array($section, $group));
        return $this->_dataContainer->get($path);
    }

    /**
     * Get available product types
     *
     * @return array
     */
    public function getAvailableProductTypes()
    {
        return $this->_dataContainer->get('order/available_product_types');
    }
}
