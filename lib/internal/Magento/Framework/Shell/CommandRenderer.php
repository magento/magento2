<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
        $command = preg_replace('/(\s+2>&1)*(\s*\|)|$/', ' 2>&1$2', $command);
        $arguments = array_map('escapeshellarg', $arguments);
        if (empty($arguments)) {
            return $command;
        }
        return vsprintf($command, $arguments);
    }
}
