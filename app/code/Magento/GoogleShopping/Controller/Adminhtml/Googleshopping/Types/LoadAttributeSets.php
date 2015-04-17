<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class LoadAttributeSets extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /*
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->layout = $layout;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Get available attribute sets
     *
     * @return \Magento\Framework\Controller\Result\Raw
     * @throws \Exception
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        return $resultRaw->setContents(
            $this->layout->getBlockSingleton('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Form')
                ->getAttributeSetsSelectElement($this->getRequest()->getParam('target_country'))
                ->toHtml()
        );
    }
}
