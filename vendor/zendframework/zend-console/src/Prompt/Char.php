<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Prompt;

class Char extends AbstractPrompt
{
    /**
     * @var string
     */
    protected $promptText = 'Please select one option ';

    /**
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @var string
     */
    protected $allowedChars = 'yn';

    /**
     * @var bool
     */
    protected $ignoreCase = true;

    /**
     * @var bool
     */
    protected $echo = true;

    /**
     * Ask the user for a single key stroke
     *
     * @param string $promptText   The prompt text to display in console
     * @param string $allowedChars A list of allowed chars (i.e. "abc12345")
     * @param bool   $ignoreCase   If true, case will be ignored and prompt will always return lower-cased response
     * @param bool   $allowEmpty   Is empty response allowed?
     * @param bool   $echo         Display the selection after user presses key
     */
    public function __construct(
        $promptText = 'Please hit a key',
        $allowedChars = '0123456789abcdefghijklmnopqrstuvwxyz',
        $ignoreCase = true,
        $allowEmpty = false,
        $echo = true
    ) {
        $this->setPromptText($promptText);
        $this->setAllowEmpty($allowEmpty);
        $this->setIgnoreCase($ignoreCase);

        if (null != $allowedChars) {
            if ($this->ignoreCase) {
                $this->setAllowedChars(strtolower($allowedChars));
            } else {
                $this->setAllowedChars($allowedChars);
            }
        }

        $this->setEcho($echo);
    }

    /**
     * Show the prompt to user and return a single char.
     *
     * @return string
     */
    public function show()
    {
        $this->getConsole()->write($this->promptText);
        $mask = $this->getAllowedChars();

        /**
         * Normalize the mask if case is irrelevant
         */
        if ($this->ignoreCase) {
            $mask = strtolower($mask);   // lowercase all
            $mask .= strtoupper($mask);  // uppercase and append
            $mask = str_split($mask);    // convert to array
            $mask = array_unique($mask); // remove duplicates
            $mask = implode("", $mask);   // convert back to string
        }

        /**
         * Read char from console
         */
        $char = $this->getConsole()->readChar($mask);

        if ($this->echo) {
            echo trim($char)."\n";
        } else {
            if ($this->promptText) {
                echo "\n";  // skip to next line but only if we had any prompt text
            }
        }

        return $this->lastResponse = $char;
    }

    /**
     * @param bool $allowEmpty
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = (bool) $allowEmpty;
    }

    /**
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * @param string $promptText
     */
    public function setPromptText($promptText)
    {
        $this->promptText = $promptText;
    }

    /**
     * @return string
     */
    public function getPromptText()
    {
        return $this->promptText;
    }

    /**
     * @param string $allowedChars
     */
    public function setAllowedChars($allowedChars)
    {
        $this->allowedChars = $allowedChars;
    }

    /**
     * @return string
     */
    public function getAllowedChars()
    {
        return $this->allowedChars;
    }

    /**
     * @param bool $ignoreCase
     */
    public function setIgnoreCase($ignoreCase)
    {
        $this->ignoreCase = (bool) $ignoreCase;
    }

    /**
     * @return bool
     */
    public function getIgnoreCase()
    {
        return $this->ignoreCase;
    }

    /**
     * @param bool $echo
     */
    public function setEcho($echo)
    {
        $this->echo = (bool) $echo;
    }

    /**
     * @return bool
     */
    public function getEcho()
    {
        return $this->echo;
    }
}
