<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Controller\Ajax;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Translate\Inline\ParserInterface;

/**
 * Ajax action for inline translation
 */
class Index implements HttpPostActionInterface
{
    /**
     * @var ParserInterface
     */
    protected $inlineParser;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var ActionFlag
     */
    private $actionFlag;

    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @param RequestInterface $request
     * @param JsonResultFactory $jsonResultFactory
     * @param ParserInterface $inlineParser
     * @param ActionFlag $actionFlag
     */
    public function __construct(
        RequestInterface $request,
        JsonResultFactory $jsonResultFactory,
        ParserInterface $inlineParser,
        ActionFlag $actionFlag
    ) {
        $this->inlineParser = $inlineParser;
        $this->request = $request;
        $this->actionFlag = $actionFlag;
        $this->jsonResultFactory = $jsonResultFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        $translate = (array)$this->request->getPost('translate');

        $jsonResult = $this->jsonResultFactory->create();
        try {
            $jsonResult->setData($this->inlineParser->processAjaxPost($translate));
        } catch (\Exception $e) {
            $jsonResult->setData(['error' => true, 'message' => $e->getMessage()]);
        }
        $this->actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);

        return $jsonResult;
    }
}
