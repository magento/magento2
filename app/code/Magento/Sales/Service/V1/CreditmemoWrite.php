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

use Magento\Sales\Service\V1\Action\CreditmemoAddComment;
use Magento\Sales\Service\V1\Action\CreditmemoCancel;
use Magento\Sales\Service\V1\Action\CreditmemoEmail;
use Magento\Sales\Service\V1\Action\CreditmemoCreate;
use Magento\Sales\Service\V1\Data\Comment;
use Magento\Sales\Service\V1\Data\Creditmemo;

/**
 * Class CreditmemoWrite
 */
class CreditmemoWrite implements CreditmemoWriteInterface
{
    /**
     * @var CreditmemoAddComment
     */
    protected $creditmemoAddComment;

    /**
     * @var CreditmemoCancel
     */
    protected $creditmemoCancel;

    /**
     * @var CreditmemoEmail
     */
    protected $creditmemoEmail;

    /**
     * @var CreditmemoCreate
     */
    protected $creditmemoCreate;

    /**
     * @param CreditmemoAddComment $creditmemoAddComment
     * @param CreditmemoCancel $creditmemoCancel
     * @param CreditmemoEmail $creditmemoEmail
     * @param CreditmemoCreate $creditmemoCreate
     */
    public function __construct(
        CreditmemoAddComment $creditmemoAddComment,
        CreditmemoCancel $creditmemoCancel,
        CreditmemoEmail $creditmemoEmail,
        CreditmemoCreate $creditmemoCreate
    ) {
        $this->creditmemoAddComment = $creditmemoAddComment;
        $this->creditmemoCancel = $creditmemoCancel;
        $this->creditmemoEmail = $creditmemoEmail;
        $this->creditmemoCreate = $creditmemoCreate;
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Comment $comment
     * @return bool
     * @throws \Exception
     */
    public function addComment(Comment $comment)
    {
        return $this->creditmemoAddComment->invoke($comment);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function cancel($id)
    {
        return $this->creditmemoCancel->invoke($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function email($id)
    {
        return $this->creditmemoEmail->invoke($id);
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Creditmemo $creditmemoDataObject
     * @throws \Exception
     * @return bool
     */
    public function create(Creditmemo $creditmemoDataObject)
    {
        return $this->creditmemoCreate->invoke($creditmemoDataObject);
    }
}
