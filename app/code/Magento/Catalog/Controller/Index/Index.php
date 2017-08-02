<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Index;

/**
 * Class \Magento\Catalog\Controller\Index\Index
 *
 * @since 2.0.0
 */
class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * Index action
     *
     * @return $this
     * @since 2.0.0
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('/');
    }
}
