<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\Backup\Controller\Adminhtml\Index\Grid
 *
 */
class Grid extends \Magento\Backup\Controller\Adminhtml\Index
{
    /**
     * Backup list action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }
}
