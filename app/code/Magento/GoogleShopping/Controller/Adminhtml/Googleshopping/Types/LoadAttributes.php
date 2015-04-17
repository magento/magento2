<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types;

class LoadAttributes extends \Magento\GoogleShopping\Controller\Adminhtml\Googleshopping\Types
{
    /**
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
     * Get Google Content attributes list
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);
        try {
            $resultRaw->setContents(
                $this->layout->createBlock('Magento\GoogleShopping\Block\Adminhtml\Types\Edit\Attributes')
                    ->setAttributeSetId($this->getRequest()->getParam('attribute_set_id'))
                    ->setTargetCountry($this->getRequest()->getParam('target_country'))
                    ->setAttributeSetSelected(true)
                    ->toHtml()
            );
        } catch (\Exception $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            // just need to output text with error
            $this->messageManager->addError(__("We can't load attributes."));
        }
        return $resultRaw;
    }
}
