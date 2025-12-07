<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestModuleMessageQueueConfiguration;

/**
 * Class for testing queue handlers.
 */
class HandlerTwo
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
