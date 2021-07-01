<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Controller\Download;

use Exception;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\Download\CustomOptionInfo;
use Magento\Sales\Model\Download;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;

class DownloadCustomOption implements HttpGetActionInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectInterface
     */
    private $redirect;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RedirectFactory
     */
    private $redirectFactory;

    /**
     * @var Download
     */
    protected $download;

    /**
     * @var CustomOptionInfo
     */
    private $searcher;

    /**
     * DownloadCustomOption constructor.
     * @param RequestInterface $request
     * @param RedirectInterface $redirect
     * @param ForwardFactory $resultForwardFactory
     * @param RedirectFactory $redirectFactory
     * @param ManagerInterface $messageManager
     * @param Download $download
     * @param CustomOptionInfo $searcher
     */
    public function __construct(
        RequestInterface $request,
        RedirectInterface $redirect,
        ForwardFactory $resultForwardFactory,
        RedirectFactory $redirectFactory,
        ManagerInterface $messageManager,
        Download $download,
        CustomOptionInfo $searcher
    ) {
        $this->request = $request;
        $this->redirect = $redirect;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->redirectFactory = $redirectFactory;
        $this->messageManager = $messageManager;
        $this->download = $download;
        $this->searcher = $searcher;
    }

    /**
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->redirectFactory->create();

        $orderItemId = (int) $this->request->getParam('order_item_id');
        $optionId = (int) $this->request->getParam('option_id');
        $quoteItemOptionId = (int) $this->request->getParam('id');

        try {
            $info = $this->searcher->search($quoteItemOptionId, $orderItemId, $optionId);
            if ($this->request->getParam('key') != $info['secret_key']) {
                $resultRedirect->setUrl($this->redirect->getRefererUrl());
                return $resultRedirect;
            }
            $this->download->downloadFile($info);
        } catch (Exception $exception) {
            $this->messageManager->addExceptionMessage($exception, 'Cannot download file');
            $resultRedirect->setUrl($this->redirect->getRefererUrl());
            return $resultRedirect;
        }
    }
}
