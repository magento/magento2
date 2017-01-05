<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
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
