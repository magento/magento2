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
namespace Magento\Sales\Service\V1;

use Magento\Sales\Service\V1\Action\OrderGet;
use Magento\Sales\Service\V1\Action\OrderList;
use Magento\Sales\Service\V1\Action\OrderCommentsList;
use Magento\Sales\Service\V1\Action\OrderGetStatus;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class OrderRead
 */
class OrderRead implements OrderReadInterface
{
    /**
     * @var OrderGet
     */
    protected $orderGet;

    /**
     * @var OrderList
     */
    protected $orderList;

    /**
     * @var OrderCommentsList
     */
    protected $orderCommentsList;

    /**
     * @var OrderGetStatus
     */
    protected $orderGetStatus;

    /**
     * @param OrderGet $orderGet
     * @param OrderList $orderList
     * @param OrderCommentsList $orderCommentsList
     * @param OrderGetStatus $orderGetStatus
     */
    public function __construct(
        OrderGet $orderGet,
        OrderList $orderList,
        OrderCommentsList $orderCommentsList,
        OrderGetStatus $orderGetStatus
    ) {
        $this->orderGet = $orderGet;
        $this->orderList = $orderList;
        $this->orderCommentsList = $orderCommentsList;
        $this->orderGetStatus = $orderGetStatus;
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\Order
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        return $this->orderGet->invoke($id);
    }

    /**
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Sales\Service\V1\Data\OrderSearchResults
     */
    public function search(SearchCriteria $searchCriteria)
    {
        return $this->orderList->invoke($searchCriteria);
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\OrderStatusHistorySearchResults
     */
    public function commentsList($id)
    {
        return $this->orderCommentsList->invoke($id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStatus($id)
    {
        return $this->orderGetStatus->invoke($id);
    }
}
