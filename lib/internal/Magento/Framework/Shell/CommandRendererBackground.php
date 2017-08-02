<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\OsInfo;

/**
 * Class \Magento\Framework\Shell\CommandRendererBackground
 *
 * @since 2.0.0
 */
class CommandRendererBackground extends CommandRenderer
{
    /**
     * @var \Magento\Framework\OsInfo
     * @since 2.0.0
     */
    protected $osInfo;

    /**
     * @param OsInfo $osInfo
     * @since 2.0.0
     */
    public function __construct(OsInfo $osInfo)
    {
        $this->osInfo = $osInfo;
    }

    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     * @since 2.0.0
     */
    public function render($command, array $arguments = [])
    {
        $command = parent::render($command, $arguments);

        return $this->osInfo->isWindows() ?
            'start /B "magento background task" ' . $command
            : str_replace('2>&1', '> /dev/null &', $command);
    }
}
