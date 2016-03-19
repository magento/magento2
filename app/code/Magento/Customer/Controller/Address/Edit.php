<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Controller\Address;

class Edit extends \Magento\Customer\Controller\Address
{
    /**
     * Customer address edit action
     *
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('form');
    }
}
