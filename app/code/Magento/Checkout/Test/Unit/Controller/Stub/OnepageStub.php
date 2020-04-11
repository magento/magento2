<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Checkout\Test\Unit\Controller\Stub;

use Magento\Checkout\Controller\Onepage;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class OnepageStub extends Onepage
{
    /**
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        // Empty method stub for test
    }
}
