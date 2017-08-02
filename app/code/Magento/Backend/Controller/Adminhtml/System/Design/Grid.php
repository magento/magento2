<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

/**
 * Class \Magento\Backend\Controller\Adminhtml\System\Design\Grid
 *
 * @since 2.0.0
 */
class Grid extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
