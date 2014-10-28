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

use Magento\Sales\Service\V1\Action\ShipmentGet;
use Magento\Sales\Service\V1\Action\ShipmentList;
use Magento\Sales\Service\V1\Action\ShipmentCommentsList;
use Magento\Sales\Service\V1\Action\ShipmentLabelGet;
use Magento\Framework\Service\V1\Data\SearchCriteria;

/**
 * Class ShipmentRead
 */
class ShipmentRead implements ShipmentReadInterface
{
    /**
     * @var ShipmentGet
     */
    protected $shipmentGet;

    /**
     * @var ShipmentList
     */
    protected $shipmentList;

    /**
     * @var ShipmentCommentsList
     */
    protected $shipmentCommentsList;

    /**
     * @var ShipmentLabelGet
     */
    protected $shipmentLabelGet;

    /**
     * @param ShipmentGet $shipmentGet
     * @param ShipmentList $shipmentList
     * @param ShipmentCommentsList $shipmentCommentsList
     * @param ShipmentLabelGet $shipmentLabelGet
     */
    public function __construct(
        ShipmentGet $shipmentGet,
        ShipmentList $shipmentList,
        ShipmentCommentsList $shipmentCommentsList,
        ShipmentLabelGet $shipmentLabelGet
    ) {
        $this->shipmentGet = $shipmentGet;
        $this->shipmentList = $shipmentList;
        $this->shipmentCommentsList = $shipmentCommentsList;
        $this->shipmentLabelGet = $shipmentLabelGet;
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\Shipment
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($id)
    {
        return $this->shipmentGet->invoke($id);
    }

    /**
     * @param \Magento\Framework\Service\V1\Data\SearchCriteria $searchCriteria
     * @return \Magento\Framework\Service\V1\Data\SearchResults
     */
    public function search(SearchCriteria $searchCriteria)
    {
        return $this->shipmentList->invoke($searchCriteria);
    }

    /**
     * @param int $id
     * @return \Magento\Sales\Service\V1\Data\CommentSearchResults
     */
    public function commentsList($id)
    {
        return $this->shipmentCommentsList->invoke($id);
    }

    /**
     * @param int $id
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getLabel($id)
    {
        return $this->shipmentLabelGet->invoke($id);
    }
}
