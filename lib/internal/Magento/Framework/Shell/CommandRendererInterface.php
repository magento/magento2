<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

/**
 * Shell command renderer
 *
 * @api
 * @since 100.0.2
 */
interface CommandRendererInterface
{
    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     */
    public function render($command, array $arguments = []);
}
