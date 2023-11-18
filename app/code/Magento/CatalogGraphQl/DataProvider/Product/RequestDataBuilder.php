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
    /**
     * @var array
     */
    private array $data;

    public function __construct()
    {
        $this->_resetState();
    }

    /**
     * Sets the data
     *
     * @param $data
     * @return void
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * Gets the data
     *
     * @param string $key
     * @return mixed|null
     */
    public function getData(string $key): mixed
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
