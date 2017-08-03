<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Adminhtml\Product\Attribute;

/**
 * Class \Magento\Catalog\Controller\Adminhtml\Product\Attribute\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Catalog\Controller\Adminhtml\Product\Attribute
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     * @since 2.0.0
     */
    public function execute()
    {
        $resultPage = $this->createActionPage();
        $resultPage->addContent(
            $resultPage->getLayout()->createBlock(\Magento\Catalog\Block\Adminhtml\Product\Attribute::class)
        );
        return $resultPage;
    }
}
