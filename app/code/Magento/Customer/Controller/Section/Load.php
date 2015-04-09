<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Section;

use Magento\Customer\Model\PrivateData\Section\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;

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
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var SectionPoolInterface
     */
    protected $sectionPool;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param RedirectFactory $redirectFactory
     * @param SectionPoolInterface $sectionPool
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RedirectFactory $redirectFactory,
        SectionPoolInterface $sectionPool
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->sectionPool = $sectionPool;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            // TODO: MAGETWO-34824 redirect correct url
            return $this->redirectFactory->create()->setPath('*/*/index');
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        try {
            $sectionNames = $this->getRequest()->getParam('sections');
            $sectionNames = $sectionNames ? \explode(',', $sectionNames) : null;

            $response = $this->sectionPool->getSectionsData(array_unique($sectionNames));
        } catch (LocalizedException $e) {
            // TODO: MAGETWO-34824 replace on const
            $resultJson->setStatusHeader(400, \Zend\Http\AbstractMessage::VERSION_11, 'Bad request');
            $response = ['message' => $e->getMessage()];
        }

        return $resultJson->setData($response);
    }
}
