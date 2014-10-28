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

use Magento\Sales\Service\V1\Data\Comment;
use Magento\Sales\Model\Order\Shipment\CommentConverter;

/**
 * Class ShipmentAddComment
 */
class ShipmentAddComment
{
    /**
     * @var \Magento\Sales\Model\Order\Shipment\CommentConverter
     */
    protected $commentConverter;

    /**
     * @param \Magento\Sales\Model\Order\Shipment\CommentConverter $commentConverter
     */
    public function __construct(CommentConverter $commentConverter)
    {
        $this->commentConverter = $commentConverter;
    }

    /**
     * Invoke shipment add comment service
     *
     * @param \Magento\Sales\Service\V1\Data\Comment $comment
     * @return bool
     * @throws \Exception
     */
    public function invoke(Comment $comment)
    {
        /** @var \Magento\Sales\Model\Order\Shipment\Comment $commentModel */
        $commentModel = $this->commentConverter->getModel($comment);
        $commentModel->save();

        return true;
    }
}
