<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Shell;

use Magento\Framework\Exception\LocalizedException;

/**
 * Shell driver encapsulates command execution and arguments escaping
 */
class Driver
{
    /** @var CommandRendererInterface */
    private $commandRenderer;

    /**
     * @param CommandRendererInterface $commandRenderer
     */
    public function __construct(CommandRendererInterface $commandRenderer)
    {
        $this->commandRenderer = $commandRenderer;
    }

    /**
     * Execute a command through the command line, passing properly escaped arguments, and return its output
     *
     * @param string $command Command with optional argument markers '%s'
     * @param string[] $arguments Argument values to substitute markers with
     * @return Response
     * @throws LocalizedException
     */

    public function execute($command, $arguments)
    {
        $disabled = explode(',', str_replace(' ', ',', ini_get('disable_functions')));
        if (in_array('exec', $disabled)) {
            throw new LocalizedException(new \Magento\Framework\Phrase("exec function is disabled."));
        }

        $command = $this->commandRenderer->render($command, $arguments);
        exec($command, $output, $exitCode);
        $output = implode(PHP_EOL, $output);
        return new Response(['output' => $output, 'exit_code' => $exitCode, 'escaped_command' => $command]);
    }
}
