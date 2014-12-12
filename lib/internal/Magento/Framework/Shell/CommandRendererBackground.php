<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Shell;

use Magento\Framework\OsInfo;

class CommandRendererBackground extends CommandRenderer
{
    /**
     * @var \Magento\Framework\OsInfo
     */
    protected $osInfo;

    /**
     * @param OsInfo $osInfo
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
     */
    public function render($command, array $arguments = [])
    {
        $command = parent::render($command, $arguments);
        return $this->osInfo->isWindows() ?
            'start /B "magento background task" ' . $command
            : $command . ' > /dev/null &';
    }
}
