<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogGraphQl\DataProvider\Product;

use Magento\Framework\ObjectManager\ResetAfterRequestInterface;

/**
 * Builds request specific Product Search Query
 */
class RequestDataBuilder implements ResetAfterRequestInterface
{
    private array $data;

    public function __construct()
    {
        $this->_resetState();
    }

    public function setData($data)
    {
        $this->data = $data;
    }

    public function getData(string $key)
    {
        return $this->data[$key] ?? null;

    }

    /**
     * @inheritDoc
     */
    public function _resetState(): void
    {
        $this->data = [];
    }
}
