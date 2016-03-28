<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

class CommandRenderer implements CommandRendererInterface
{
    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function render($command, array $arguments = [])
    {
        $arguments = array_map('escapeshellarg', $arguments);
        $command = preg_replace('/\s?\||$/', ' 2>&1$0', $command);
        $command = vsprintf($command, $arguments);
        return $command;
    }
}
