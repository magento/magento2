<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Utility\PartialNamespace;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ResponseInterface;

class Baz implements ActionInterface
{
    /**
     * Execute action based on request and return result
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {

    }
}
