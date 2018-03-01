<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Setup\Declaration\Schema;

/**
 * CLI or Ui params transport object.
 */
class Request
{
    /**
     * Option to enable dump functionality for safe mode.
     */
    const DUMP_ENABLE_OPTIONS = "dump_enable";

    /**
     * @var  bool
     */
    private $dumpEnable = false;

    /**
     * Constructor.
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        if (isset($request[static::DUMP_ENABLE_OPTIONS])) {
            $this->dumpEnable = (bool) $request[static::DUMP_ENABLE_OPTIONS];
        }
    }

    /**
     * Check whether dump is enabled.
     *
     * @return boolean
     */
    public function isDumpEnabled()
    {
        return $this->dumpEnable;
    }
}
