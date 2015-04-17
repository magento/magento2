<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items;

use Magento\Backend\App\Action;
use Magento\Framework\Notification\NotifierInterface;

class Grid extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Items
{
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param Action\Context $context
     * @param NotifierInterface $notifier
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        Action\Context $context,
        NotifierInterface $notifier,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
        parent::__construct($context, $notifier, $urlEncoder);
    }

    /**
     * Grid with Google Content items
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        /** @var \Magento\GoogleShopping\Block\Adminhtml\Items\Item $block */
        $block = $this->layout->createBlock('Magento\GoogleShopping\Block\Adminhtml\Items\Item');
        return $resultRaw->setContents($block->setIndex($this->getRequest()->getParam('index'))->toHtml());
    }
}
