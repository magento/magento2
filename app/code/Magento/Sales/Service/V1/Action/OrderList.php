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
namespace Magento\Sales\Service\V1\Action;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Service\V1\Data\OrderMapper;
use Magento\Sales\Service\V1\Data\OrderSearchResultsBuilder;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class OrderList
 */
class OrderList
{
    /**
     * @var OrderRepository
     */
    protected $orderRepository;

    /**
     * @var OrderMapper
     */
    protected $orderMapper;

    /**
     * @var OrderSearchResultsBuilder
     */
    protected $searchResultsBuilder;

    /**
     * @param OrderRepository $orderRepository
     * @param OrderMapper $orderMapper
     * @param OrderSearchResultsBuilder $searchResultsBuilder
     */
    public function __construct(
        OrderRepository $orderRepository,
        OrderMapper $orderMapper,
        OrderSearchResultsBuilder $searchResultsBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->orderMapper = $orderMapper;
        $this->searchResultsBuilder = $searchResultsBuilder;
    }

    /**
     * Invoke OrderList service
     *
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Sales\Service\V1\Data\OrderSearchResults
     */
    public function invoke(SearchCriteria $searchCriteria)
    {
        $orders = [];
        foreach ($this->orderRepository->find($searchCriteria) as $order) {
            $orders[] = $this->orderMapper->extractDto($order);
        }
        return $this->searchResultsBuilder->setItems($orders)
            ->setTotalCount(count($orders))
            ->setSearchCriteria($searchCriteria)
            ->create();
    }
}
