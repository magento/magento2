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
 * @package     Magento_Sales
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Sales\Model\Order;

/**
 * Shipment collection updater on shipments tab of view order page
 */
class ShipmentsUpdater implements \Magento\Core\Model\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Core\Model\Registry
     */
    protected $_registryManager;

    /**
     * @param \Magento\Core\Model\Registry $registryManager
     */
    public function __construct(\Magento\Core\Model\Registry $registryManager)
    {
        $this->_registryManager = $registryManager;
    }

    /**
     * Add order filter
     *
     * @param \Magento\Sales\Model\Resource\Order\Shipment\Grid\Collection $argument
     * @return mixed
     * @throws \DomainException
     */
    public function update($argument)
    {
        $order = $this->_registryManager->registry('current_order');

        if (!$order) {
            throw new \DomainException('Undefined order object');
        }

        $argument->setOrderFilter($order->getId());
        return $argument;
    }
}
