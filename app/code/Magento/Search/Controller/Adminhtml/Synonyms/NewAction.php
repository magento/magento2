<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Adminhtml\Synonyms;

class NewAction extends \Magento\Search\Controller\Adminhtml\Synonyms
{
    /**
     * Create new synonyms group action
     *
     * @return \Magento\Backend\Model\View\Result\Forward
     */
    public function execute()
    {
        $forward = $this->forwardFactory->create();
        $forward->forward('edit');
        return $forward;
    }
}
