<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Section;

use Magento\Customer\Model\Section\SectionPoolInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JSONFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Customer section controller
 */
class Load extends \Magento\Framework\App\Action\Action
{
    /**
     * @var JSONFactory
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
     * @param JSONFactory $resultJsonFactory
     * @param RedirectFactory $redirectFactory
     * @param SectionPoolInterface $sectionPool
     */
    public function __construct(
        Context $context,
        JSONFactory $resultJsonFactory,
        RedirectFactory $redirectFactory,
        SectionPoolInterface $sectionPool
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->redirectFactory = $redirectFactory;
        $this->sectionPool = $sectionPool;
    }

    /**
     * @return \Magento\Framework\Controller\Result\JSON|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        if (!$this->getRequest()->isAjax()) {
            // TODO: MAGETWO-34824 redirect correct url
            return $this->redirectFactory->create()->setPath('*/*/index');
        }
        /** @var \Magento\Framework\Controller\Result\JSON $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        try {
            $sectionNames = $this->getRequest()->getParam('sections');
            $sections = $sectionNames ? $this->sectionPool->getSections(\explode(',', $sectionNames))
                : $this->sectionPool->getAllSections();

            $response = [];
            foreach ($sections as $sectionName => $section) {
                $response[$sectionName] = $section->getData();
            }
        } catch (LocalizedException $e) {
            $resultJson->setStatusHeader(400, \Zend\Http\AbstractMessage::VERSION_11, 'Bad request');
            $response = ['message' => $e->getMessage()];
        }

        return $resultJson->setData($response);
    }
}
