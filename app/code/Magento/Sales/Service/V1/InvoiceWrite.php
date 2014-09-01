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

use Magento\Sales\Service\V1\Action\InvoiceAddComment;
use Magento\Sales\Service\V1\Action\InvoiceVoid;
use Magento\Sales\Service\V1\Action\InvoiceEmail;
use Magento\Sales\Service\V1\Action\InvoiceCapture;
use Magento\Sales\Service\V1\Action\InvoiceCreate;
use Magento\Sales\Service\V1\Data\Comment;

/**
 * Class InvoiceWrite
 */
class InvoiceWrite implements InvoiceWriteInterface
{
    /**
     * @var InvoiceAddComment
     */
    protected $invoiceAddComment;

    /**
     * @var InvoiceVoid
     */
    protected $invoiceVoid;

    /**
     * @var InvoiceEmail
     */
    protected $invoiceEmail;

    /**
     * @var InvoiceCapture
     */
    protected $invoiceCapture;

    /**
     * @var InvoiceCreate
     */
    protected $invoiceCreate;

    /**
     * @param InvoiceAddComment $invoiceAddComment
     * @param InvoiceVoid $invoiceVoid
     * @param InvoiceEmail $invoiceEmail
     * @param InvoiceCapture $invoiceCapture
     * @param InvoiceCreate $invoiceCreate
     */
    public function __construct(
        InvoiceAddComment $invoiceAddComment,
        InvoiceVoid $invoiceVoid,
        InvoiceEmail $invoiceEmail,
        InvoiceCapture $invoiceCapture,
        InvoiceCreate $invoiceCreate
    ) {
        $this->invoiceAddComment = $invoiceAddComment;
        $this->invoiceVoid = $invoiceVoid;
        $this->invoiceEmail = $invoiceEmail;
        $this->invoiceCapture = $invoiceCapture;
        $this->invoiceCreate = $invoiceCreate;
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Comment $comment
     * @return bool
     * @throws \Exception
     */
    public function addComment(Comment $comment)
    {
        return $this->invoiceAddComment->invoke($comment);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function void($id)
    {
        return $this->invoiceVoid->invoke($id);
    }

    /**
     * @param int $id
     * @return bool
     */
    public function email($id)
    {
        return $this->invoiceEmail->invoke($id);
    }

    /**
     * @param int $id
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function capture($id)
    {
        return $this->invoiceCapture->invoke($id);
    }

    /**
     * @param \Magento\Sales\Service\V1\Data\Invoice $invoiceDataObject
     * @return bool
     * @throws \Exception
     */
    public function create(\Magento\Sales\Service\V1\Data\Invoice $invoiceDataObject)
    {
        return $this->invoiceCreate->invoke($invoiceDataObject);
    }
}
