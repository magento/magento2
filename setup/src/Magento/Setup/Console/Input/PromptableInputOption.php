<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Console\Input;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;

class PromptableInputOption extends InputOption
{

    /**
     * Ignore the default param since we will prompt for it instead
     *
     * @param string
     * @param string|array
     * @param int
     * @param string
     * @param mixed
     */
    public function __construct($name, $shortcut = null, $mode = null, $description = '', $default = null)
    {
        parent::__construct($name, $shortcut, $mode, $description, null);
    }

    /**
     * Prompt for the value to be used if none given, instead of using a default
     *
     * @return mixed
     */
    public function getDefault()
    {
        $default = parent::getDefault();
        if ($default !== null) {
            return $default;
        }
        $dialog = new DialogHelper;
        $this->setDefault($dialog->ask(
            new ConsoleOutput,
            '<info>' . ucwords(str_replace('-', ' ', $this->getName())) . '</info>: '
        ));
        return parent::getDefault();
    }
}
