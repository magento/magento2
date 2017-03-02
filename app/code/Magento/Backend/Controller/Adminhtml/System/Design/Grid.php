<?php
/**
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Controller\Adminhtml\System\Design;

class Grid extends \Magento\Backend\Controller\Adminhtml\System\Design
{
    /**
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        return $this->resultLayoutFactory->create();
    }
}
