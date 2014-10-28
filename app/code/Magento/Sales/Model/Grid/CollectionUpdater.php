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

namespace Magento\Sales\Model\Grid;


class CollectionUpdater implements \Magento\Framework\View\Layout\Argument\UpdaterInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryManager;

    /**
     * @param \Magento\Framework\Registry $registryManager
     */
    public function __construct(\Magento\Framework\Registry $registryManager)
    {
        $this->registryManager = $registryManager;
    }

    /**
     * Update grid collection according to chosen order
     *
     * @param \Magento\Sales\Model\Resource\Transaction\Grid\Collection $argument
     * @return \Magento\Sales\Model\Resource\Transaction\Grid\Collection
     */
    public function update($argument)
    {
        $order = $this->registryManager->registry('current_order');
        if ($order) {
            $argument->setOrderFilter($order->getId());
        }
        $argument->addOrderInformation(array('increment_id'));

        return $argument;
    }
}
