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
 * @since 2.0.0
 */
interface CommandRendererInterface
{
    /**
     * Render command with arguments
     *
     * @param string $command
     * @param array $arguments
     * @return string
     * @since 2.0.0
     */
    public function render($command, array $arguments = []);
}
