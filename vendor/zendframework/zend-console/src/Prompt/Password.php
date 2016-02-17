<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Prompt;

final class Password extends AbstractPrompt
{
    /**
     * @var string
     */
    private $promptText;

    /**
     * @var bool
     */
    private $echo;

    /**
     * Ask the user for a password
     *
     * @param string $promptText   The prompt text to display in console
     * @param bool   $echo         Display the selection after user presses key
     */
    public function __construct($promptText = 'Password: ', $echo = false)
    {
        $this->promptText = (string) $promptText;
        $this->echo       = (bool) $echo;
    }

    /**
     * Show the prompt to user and return a string.
     *
     * @return string
     */
    public function show()
    {
        $console = $this->getConsole();

        $console->writeLine($this->promptText);

        $password = '';

        /**
         * Read characters from console
         */
        while (true) {
            $char = $console->readChar();

            $console->clearLine();

            if (PHP_EOL == $char) {
                break;
            }

            $password .= $char;

            if ($this->echo) {
                $console->write(str_repeat('*', strlen($password)));
            }
        }

        return $password;
    }
}
