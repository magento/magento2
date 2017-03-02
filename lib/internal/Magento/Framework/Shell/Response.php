<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\DataObject;

/**
 * Encapsulates output of shell command
 */
class Response extends DataObject
{
    /**
     * Get output
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getOutput()
    {
        return $this->getData('output');
    }

    /**
     * Get exit code
     *
     * @return int
     * @codeCoverageIgnore
     */
    public function getExitCode()
    {
        return $this->getData('exit_code');
    }

    /**
     * Get escaped command
     *
     * @return string
     * @codeCoverageIgnore
     */
    public function getEscapedCommand()
    {
        return $this->getData('escaped_command');
    }
}
