<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldFrontendUi\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Webapi\Rest\Request;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Mod\HelloWorld\Model\ExtraCommentSaver;

/**
 * Extra comment ajax controller.
 */
class Index extends Action implements HttpPostActionInterface
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var CurrentCustomer
     */
    private $session;

    /**
     * @var ResultFactory
     */
    private $resultFactory;

    /**
     * @var ExtraCommentSaver
     */
    private $commentSaver;

    /**
     * Controller constructor.
     *
     * @param Context $context
     * @param ResultFactory $resultFactory
     * @param Request $request
     * @param CurrentCustomer $session
     * @param ExtraCommentSaver $commentSaver
     */
    public function __construct(
        Context $context,
        ResultFactory $resultFactory,
        Request $request,
        CurrentCustomer $session,
        ExtraCommentSaver $commentSaver
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
        $this->request = $request;
        $this->session = $session;
        $this->commentSaver = $commentSaver;
    }

    /**
     * Gets extra comments from ajax.
     *
     * @throws CouldNotSaveException
     */
    public function execute()
    {
        $comment = $this->request->getParam('comment');
        $sku = $this->request->getParam('sku');
        $customerId = (int)$this->session->getCustomerId();
        if (!empty($comment) && $comment !== '') {
            $this->commentSaver->execute($customerId, $sku, $comment);
        }
    }
}
