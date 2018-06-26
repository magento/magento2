<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdminUi\Model;

/**
 * Mass assign session storage
 * @see \Magento\InventoryAdminUi\Controller\Adminhtml\Source\BulkAssign
 * @see \Magento\InventoryAdminUi\Controller\Adminhtml\Source\BulkUnassign
 * @see \Magento\InventoryAdminUi\Controller\Adminhtml\Stock\BulkTransfer
 */
class BulkSessionProductsStorage
{
    /**
     * @var \Magento\Backend\Model\Session\Proxy
     */
    private $session;

    /**
     * @param \Magento\Backend\Model\Session\Proxy $session
     */
    public function __construct(
        \Magento\Backend\Model\Session\Proxy $session
    ) {
        $this->session = $session;
    }

    /**
     * Set product SKUs
     * @param array $productIds
     */
    public function setProductsSkus(array $productIds): void
    {
        $this->session->setProductSkus($productIds);
    }

    /**
     * Get selected product SKUs
     * @return array
     */
    public function getProductsSkus(): array
    {
        return $this->session->getProductSkus();
    }
}
