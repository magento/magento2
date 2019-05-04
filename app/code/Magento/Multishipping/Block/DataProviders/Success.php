<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Multishipping\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Multishipping\Block\Checkout\Results;

/**
 * Provides additional data for multishipping checkout success step.
 */
class Success extends Results implements ArgumentInterface
{

}
