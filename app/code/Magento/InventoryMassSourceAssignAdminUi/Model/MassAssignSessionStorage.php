<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryMassSourceAssignAdminUi\Model;

/**
 * Mass assign session storage
 * @see \Magento\InventoryMassSourceAssignAdminUi\Controller\Adminhtml\Source\MassAssign
 */
class MassAssignSessionStorage
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
     * Set product ids
     * @param array $productIds
     */
    public function setProductIds(array $productIds): void
    {
        $this->session->setProductIds($productIds);
    }

    /**
     * Get selected product ids
     * @return array
     */
    public function getProductIds(): array
    {
        return $this->session->getProductIds();
    }
}
