<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\App\Action;

use Magento\Framework\App\ActionInterface;

/**
 * Marker for actions processing HEAD requests.
 *
 * @deprecated 102.0.2 Both GET and HEAD requests map to HttpGetActionInterface
 */
interface HttpHeadActionInterface extends ActionInterface
{

}
