<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Logger\Configuration;

use Monolog\Handler\HandlerInterface;

/**
 * Provides global logging Configuration (e.g. non channel specific)
 */
interface LogConfigurationProviderInterface
{
    /**
     * Get Handler By Name
     *
     * @param string $key
     * @return HandlerInterface
     */
    public function getHandlerByKey(string $key): HandlerInterface;

    /**
     * Get Processor By Name
     *
     * @param string $key
     * @return object
     */
    public function getProcessorByKey(string $key);
}
