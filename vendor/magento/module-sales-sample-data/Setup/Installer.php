<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSampleData\Setup;

use Magento\Framework\Setup;

class Installer implements Setup\SampleData\InstallerInterface
{
    /**
     * @var \Magento\SalesSampleData\Model\Order;
     */
    protected $order;

    /**
     * @param \Magento\SalesSampleData\Model\Order $order
     */
    public function __construct(\Magento\SalesSampleData\Model\Order $order)
    {
        $this->order = $order;
    }

    /**
     * @inheritdoc
     */
    public function install()
    {
        $this->order->install(['Magento_SalesSampleData::fixtures/orders.csv']);
    }
}
