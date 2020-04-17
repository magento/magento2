<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Controller\Section;

use Laminas\Http\AbstractMessage;
use Laminas\Http\Response;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Json as JsonResult;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;

/**
 * Endpoint `customer/section/load` responsible for reloading sections of Customer's Local Storage
 */
class Load implements HttpGetActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var SectionPoolInterface
     */
    private $sectionPool;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param JsonFactory $resultJsonFactory
     * @param SectionPoolInterface $sectionPool
     * @param Escaper $escaper
     */
    public function __construct(
        RequestInterface $request,
        JsonFactory $resultJsonFactory,
        SectionPoolInterface $sectionPool,
        Escaper $escaper
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sectionPool = $sectionPool;
        $this->escaper = $escaper;
        $this->request = $request;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var JsonResult $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        try {
            $sectionNames = $this->request->getParam('sections');
            $sectionNames = $sectionNames ? array_unique(\explode(',', $sectionNames)) : null;

            $forceNewSectionTimestamp = $this->request->getParam('force_new_section_timestamp');
            if ('false' === $forceNewSectionTimestamp) {
                $forceNewSectionTimestamp = false;
            }
            $response = $this->sectionPool->getSectionsData($sectionNames, (bool)$forceNewSectionTimestamp);
        } catch (\Exception $e) {
            $resultJson->setStatusHeader(Response::STATUS_CODE_400, AbstractMessage::VERSION_11, 'Bad Request');
            $response = ['message' => $this->escaper->escapeHtml($e->getMessage())];
        }

        return $resultJson->setData($response);
    }
}
