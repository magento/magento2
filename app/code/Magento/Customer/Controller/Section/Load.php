<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Section;

use Magento\Customer\CustomerData\Section\Identifier;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Customer section controller
 */
class Load extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var Identifier
     * @deprecated 100.2.0
     */
    protected $sectionIdentifier;

    /**
     * @var SectionPoolInterface
     */
    protected $sectionPool;

    /**
     * @var \Magento\Framework\Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Identifier $sectionIdentifier
     * @param SectionPoolInterface $sectionPool
     * @param Escaper $escaper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Identifier $sectionIdentifier,
        SectionPoolInterface $sectionPool,
        \Magento\Framework\Escaper $escaper = null
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sectionIdentifier = $sectionIdentifier;
        $this->sectionPool = $sectionPool;
        $this->escaper = $escaper ?: $this->_objectManager->get(\Magento\Framework\Escaper::class);
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);
        try {
            $sectionNames = $this->getRequest()->getParam('sections');
            $sectionNames = $sectionNames ? array_unique(\explode(',', $sectionNames)) : null;

            $forceNewSectionTimestamp = $this->getRequest()->getParam('force_new_section_timestamp');
            if ('false' === $forceNewSectionTimestamp) {
                $forceNewSectionTimestamp = false;
            }
            $response = $this->sectionPool->getSectionsData($sectionNames, (bool)$forceNewSectionTimestamp);
        } catch (\Exception $e) {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = ['message' => $this->escaper->escapeHtml($e->getMessage())];
        }

        return $resultJson->setData($response);
    }
}
