<?php
/**
 * Created by PhpStorm.
 * User: tschampelb
 * Date: 26.10.17
 * Time: 11:53
 */
declare(strict_types=1);

namespace Magento\InventorySales\Model;

use Magento\InventorySales\Model\ResourceModel\SalesChannelsResolver;

class GetSalesChannelByStock implements GetSalesChannelsByStockInterface
{
    /** @var SalesChannelsResolver  */
    protected $salesChannelsResolver;


    public function __construct(
        SalesChannelsResolver $salesChannelsResolver)
    {
        $this->salesChannelsResolver = $salesChannelsResolver;
    }

    public function get(int $stockId) : array
    {
        //return array();
        return $this->salesChannelsResolver->resolve($stockId);
    }

}