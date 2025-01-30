<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

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

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        $this->_resetState();
    }

    /**
     * Sets request data
     *
     * @param array $data
     * @return void
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    /**
     * Gets request data
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
