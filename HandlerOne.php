<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestModuleMessageQueueConfiguration;

/**
 * Class for testing queue handlers.
 */
class HandlerOne
{
    /**
     * Return true.
     *
     * @return bool
     */
    public function handlerMethodOne()
    {
        return true;
    }

    /**
     * Return true.
     *
     * @return bool
     */
    public function handlerMethodTwo()
    {
        return true;
    }

    /**
     * Return true.
     *
     * @return bool
     */
    public function handlerMethodThree()
    {
        return true;
    }
}
