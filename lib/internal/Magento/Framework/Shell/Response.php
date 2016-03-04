<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\DataObject;

/**
 *
 */
class Response extends DataObject
{
    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getOutput()
    {
        return $this->getData('output');
    }

    /**
     * @return int
     * @codeCoverageIgnore
     */
    public function getExitCode()
    {
        return $this->getData('exit_code');
    }

    /**
     * @return string
     * @codeCoverageIgnore
     */
    public function getEscapedCommand()
    {
        return $this->getData('escaped_command');
    }
}
