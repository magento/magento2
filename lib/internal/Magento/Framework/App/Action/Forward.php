<?php
/**
 * Forward action class
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Action;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;

class Forward extends AbstractAction
{
    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function dispatch(RequestInterface $request)
    {
        $this->request = $request;
        return $this->execute();
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $this->request->setDispatched(false);
        return $this->_response;
    }
}
