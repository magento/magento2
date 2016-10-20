<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Section;

use Magento\Customer\CustomerData\Section\Identifier;
use Magento\Customer\CustomerData\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Escaper;

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
     */
    protected $sectionIdentifier;

    /**
     * @var SectionPoolInterface
     */
    protected $sectionPool;

    /**
     * @var  Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param Identifier $sectionIdentifier
     * @param SectionPoolInterface $sectionPool
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Identifier $sectionIdentifier,
        SectionPoolInterface $sectionPool
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->sectionIdentifier = $sectionIdentifier;
        $this->sectionPool = $sectionPool;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        try {
            $sectionNames = $this->getRequest()->getParam('sections');
            $sectionNames = $sectionNames ? array_unique(\explode(',', $sectionNames)) : null;

            $updateSectionId = $this->getRequest()->getParam('update_section_id');
            if ('false' === $updateSectionId) {
                $updateSectionId = false;
            }
            $response = $this->sectionPool->getSectionsData($sectionNames, (bool)$updateSectionId);
        } catch (\Exception $e) {
            $resultJson->setStatusHeader(
                \Zend\Http\Response::STATUS_CODE_400,
                \Zend\Http\AbstractMessage::VERSION_11,
                'Bad Request'
            );
            $response = ['message' => $this->getEscaper()->escapeHtml($e->getMessage())];
        }

        return $resultJson->setData($response);
    }

    /**
     * @deprecated
     * @return Escaper
     */
    private function getEscaper()
    {
        if ($this->escaper == null) {
            $this->escaper = $this->_objectManager->get(Escaper::class);
        }
        return $this->escaper;
    }
}
