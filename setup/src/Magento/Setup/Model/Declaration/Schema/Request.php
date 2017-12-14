<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Model\Declaration\Schema;

/**
 * Object for transporting CLI or Ui params
 */
class Request
{
    /**
     * Option that says that we are in safe mode and should enable dump functionality
     */
    const DUMP_ENABLE_OPTIONS = "dump_enable";

    /**
     * @var  bool
     */
    private $dumpEnable = false;

    /**
     * @param array $request
     */
    public function __construct(array $request)
    {
        if (isset($request[static::DUMP_ENABLE_OPTIONS])) {
            $this->dumpEnable = (bool) $request[static::DUMP_ENABLE_OPTIONS];
        }
    }

    /**
     * Check whether dump is enabled or not
     *
     * @return boolean
     */
    public function isDumpEnabled()
    {
        return $this->dumpEnable;
    }
}
