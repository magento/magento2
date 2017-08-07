<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\DataObject;

/**
 * Encapsulates output of shell command
 * @since 2.1.0
 */
class Response extends DataObject
{
    /**
     * Get output
     *
     * @return string
     * @codeCoverageIgnore
     * @since 2.1.0
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
     * @since 2.1.0
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
     * @since 2.1.0
     */
    public function getEscapedCommand()
    {
        return $this->getData('escaped_command');
    }
}
