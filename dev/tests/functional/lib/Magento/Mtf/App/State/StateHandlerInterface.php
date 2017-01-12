<?php
/**
 * Copyright © 2017 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\App\State;

/**
 * Interface StateHandlerInterface
 */
interface StateHandlerInterface
{
    /**
     * Perform app state change before run
     *
     * @param AbstractState $state
     * @return mixed
     */
    public function execute(AbstractState $state);
}
