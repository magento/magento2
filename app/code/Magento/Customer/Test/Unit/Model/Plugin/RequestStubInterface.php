<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model\Plugin;

use Magento\Framework\App\HttpRequestInterface;
use Magento\Framework\App\RequestInterface;

interface RequestStubInterface extends HttpRequestInterface, RequestInterface
{
}
