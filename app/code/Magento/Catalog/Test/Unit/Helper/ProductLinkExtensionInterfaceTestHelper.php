<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Helper;

use Magento\Catalog\Api\Data\ProductLinkExtensionInterface;

/**
 * Test helper for Magento\Catalog\Api\Data\ProductLinkExtensionInterface
 *
 * Implements ProductLinkExtensionInterface to provide custom methods for testing
 */
class ProductLinkExtensionInterfaceTestHelper implements ProductLinkExtensionInterface
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * Constructor
     */
    public function __construct()
    {
        // No parent constructor to call for interface
    }

    /**
     * Custom setQty method for testing
     *
     * @param mixed $qty
     * @return self
     */
    public function setQty($qty): self
    {
        $this->data['qty'] = $qty;
        return $this;
    }

    /**
     * Custom getQty method for testing
     *
     * @return mixed
     */
    public function getQty()
    {
        return $this->data['qty'] ?? null;
    }
}
