<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\SuggestAttributeSets
 *
 * @since 2.0.0
 */
class SuggestAttributeSets extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::sets';
    
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     * @since 2.0.0
     */
    protected $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\Product\AttributeSet\SuggestedSet
     * @since 2.0.0
     */
    protected $suggestedSet;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Catalog\Model\Product\AttributeSet\SuggestedSet $suggestedSet
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Catalog\Model\Product\AttributeSet\SuggestedSet $suggestedSet
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->suggestedSet = $suggestedSet;
    }

    /**
     * Action for attribute set selector
     *
     * @return \Magento\Framework\Controller\Result\Json
     * @since 2.0.0
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultJson->setData(
            $this->suggestedSet->getSuggestedSets($this->getRequest()->getParam('label_part'))
        );
        return $resultJson;
    }
}
