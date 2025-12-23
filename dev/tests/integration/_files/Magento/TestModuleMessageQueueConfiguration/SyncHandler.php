<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
namespace Magento\TestModuleMessageQueueConfiguration;

/**
 * Class for testing synchronous queue handlers.
 */
class SyncHandler
{
    /**
     * @param string
     * @return string
     */
    public function methodWithStringParam($param)
    {
        return 'Processed: ' . $param;
    }

    /**
     * @param bool
     * @return bool
     */
    public function methodWithBoolParam($param)
    {
        return !$param;
    }

    /**
     * @param mixed
     * @return mixed
     */
    public function methodWithMixedParam($param)
    {
        return $param;
    }
}
