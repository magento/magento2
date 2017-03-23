<?php
/**
 * Forward action class
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Forward extends AbstractAction
{
    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dispatch(RequestInterface $request)
    {
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->_request->setDispatched(false);
        return $this->_response;
    }
}
