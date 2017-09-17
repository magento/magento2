<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Inventory\Ui\Component\Control\Stock;

use Magento\InventoryApi\Api\Data\StockInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Request\Http as HttpRequest;

/**
 * Class GenericButton
 */
class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @param UrlInterface $urlBuilder
     * @param HttpRequest $request
     */
    public function __construct(UrlInterface $urlBuilder, HttpRequest $request)
    {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    /**
     * Get stock id
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->request->getParam(StockInterface::STOCK_ID) ?? null;
    }

    /**
     * Get button url base on route and parameters
     *
     * @param   string $route
     * @param   array $params
     *
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}