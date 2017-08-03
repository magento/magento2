<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

/**
 * Class \Magento\Framework\Shell\CommandRenderer
 *
 * @since 2.0.0
 */
class CommandRenderer implements CommandRendererInterface
{
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
        $arguments = array_map('escapeshellarg', $arguments);
        $command = preg_replace('/\s?\||$/', ' 2>&1$0', $command);
        if (empty($arguments)) {
            return $command;
        }
        return vsprintf($command, $arguments);
    }
}
