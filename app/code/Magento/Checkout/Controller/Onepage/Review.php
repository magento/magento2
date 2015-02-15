<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Controller\Onepage;

use Magento\Checkout\Controller\Onepage;

class Review extends Onepage
{
    /**
     * {@inheritdoc}
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if ($this->_expireAjax()) {
            return $this->_ajaxRedirectResponse();
        }

        return $this->resultLayoutFactory->create();
    }
}
