<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
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
