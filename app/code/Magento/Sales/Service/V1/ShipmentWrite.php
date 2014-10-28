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

use Magento\Sales\Service\V1\Action\ShipmentAddTrack;
use Magento\Sales\Service\V1\Action\ShipmentRemoveTrack;
use Magento\Sales\Service\V1\Action\ShipmentEmail;
use Magento\Sales\Service\V1\Action\ShipmentAddComment;
use Magento\Sales\Service\V1\Action\ShipmentCreate;
use Magento\Sales\Service\V1\Data\ShipmentTrack;
use Magento\Sales\Service\V1\Data\Comment;

/**
 * Class ShipmentWrite
 */
class ShipmentWrite implements ShipmentWriteInterface
{
    /**
     * @var ShipmentAddTrack
     */
    protected $shipmentAddTrack;

    /**
     * @var ShipmentRemoveTrack
     */
    protected $shipmentRemoveTrack;

    /**
     * @var ShipmentEmail
     */
    protected $shipmentEmail;

    /**
     * @var ShipmentAddComment
     */
    protected $shipmentAddComment;

    /**
     * @var ShipmentCreate
     */
    protected $shipmentCreate;

    /**
     * @param ShipmentAddTrack $shipmentAddTrack
     * @param ShipmentRemoveTrack $shipmentRemoveTrack
     * @param ShipmentEmail $shipmentEmail
     * @param ShipmentAddComment $shipmentAddComment
     * @param ShipmentCreate $shipmentCreate
     */
    public function __construct(
        ShipmentAddTrack $shipmentAddTrack,
        ShipmentRemoveTrack $shipmentRemoveTrack,
        ShipmentEmail $shipmentEmail,
        ShipmentAddComment $shipmentAddComment,
        ShipmentCreate $shipmentCreate
    ) {
        $this->shipmentAddTrack = $shipmentAddTrack;
        $this->shipmentRemoveTrack = $shipmentRemoveTrack;
        $this->shipmentEmail = $shipmentEmail;
        $this->shipmentAddComment = $shipmentAddComment;
        $this->shipmentCreate = $shipmentCreate;
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\ShipmentTrack $track
     * @return bool
     * @throws \Exception
     */
    public function addTrack(ShipmentTrack $track)
    {
        return $this->shipmentAddTrack->invoke($track);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Exception
     */
    public function removeTrack($id)
    {
        return $this->shipmentRemoveTrack->invoke($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function email($id)
    {
        return $this->shipmentEmail->invoke($id);
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Comment $comment
     * @return bool
     * @throws \Exception
     */
    public function addComment(Comment $comment)
    {
        return $this->shipmentAddComment->invoke($comment);
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Shipment $shipmentDataObject
     * @return bool
     * @throws \Exception
     */
    public function create(\Magento\Sales\Service\V1\Data\Shipment $shipmentDataObject)
    {
        return $this->shipmentCreate->invoke($shipmentDataObject);
    }
}
