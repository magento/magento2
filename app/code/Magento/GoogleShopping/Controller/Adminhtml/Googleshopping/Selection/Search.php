<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Selection;

class Search extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
        parent::__construct($context);
    }

    /**
     * Search result grid with available products for Google Content
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        return $resultRaw->setContents(
            $this->layout->createBlock('Magento\GoogleShopping\Block\Adminhtml\Items\Product')
                ->setIndex($this->getRequest()->getParam('index'))->setFirstShow(true)->toHtml()
        );
    }
}
