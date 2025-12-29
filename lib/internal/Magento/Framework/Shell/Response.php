<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
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
