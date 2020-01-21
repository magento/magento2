<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQlPlayground\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\State;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 *
 * @package Magento\GraphQlPlayground\Controller\Index
 */
class Index extends Action implements HttpGetActionInterface, HttpPostActionInterface
{
    /** 404 Controller Path */
    const NO_ROUTE_CONTROLLER = 'noroute';

    /**
     * @var State
     */
    private $appState;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param PageFactory $pageFactory
     * @param State $appState
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        State $appState,
        LoggerInterface $logger
    ) {
        $this->appState = $appState;
        $this->pageFactory = $pageFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Controller redirect to 404 on production mode else create graphql playground layout
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $result = null;
        try {
            if ($this->appState->getAreaCode() == State::MODE_PRODUCTION) {
                $result = $this->resultRedirectFactory->create();
                $result->setPath(self::NO_ROUTE_CONTROLLER);
            } else {
                $result = $this->pageFactory->create();
            }
        } catch (LocalizedException $localizedException) {
            $this->logger->error($localizedException->getMessage());
        }
        return $result;
    }
}
