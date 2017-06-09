<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Console;

use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;

class Input extends ArgvInput
{
    /**
     * Prompt for option value and set as default
     *
     * @param  DialogHelper    $dialog
     * @param  OutputInterface $output
     * @param  string          $name
     * @param  boolean         $isHidden
     * @return void
     */
    public function promptForOption(DialogHelper $dialog, OutputInterface $output, $name, $isHidden = false)
    {
        $method = $isHidden ? 'askHiddenResponse' : 'ask';
        $this->definition->getOption($name)->setDefault($dialog->$method(
            $output,
            '<info>' . ucwords(str_replace('-', ' ', $name)) . '</info>: '
        ));
    }
}
