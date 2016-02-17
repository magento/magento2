<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Prompt;

class Line extends AbstractPrompt
{
    /**
     * @var string
     */
    protected $promptText = 'Please enter value: ';

    /**
     * @var bool
     */
    protected $allowEmpty = false;

    /**
     * @var int
     */
    protected $maxLength = 2048;

    /**
     * Ask the user for an answer (a line of text)
     *
     * @param string    $promptText     The prompt text to display in console
     * @param bool      $allowEmpty     Is empty response allowed?
     * @param int       $maxLength      Maximum response length
     */
    public function __construct($promptText = 'Please enter value: ', $allowEmpty = false, $maxLength = 2048)
    {
        if ($promptText !== null) {
            $this->setPromptText($promptText);
        }

        if ($allowEmpty !== null) {
            $this->setAllowEmpty($allowEmpty);
        }

        if ($maxLength !== null) {
            $this->setMaxLength($maxLength);
        }
    }

    /**
     * Show the prompt to user and return the answer.
     *
     * @return string
     */
    public function show()
    {
        do {
            $this->getConsole()->write($this->promptText);
            $line = $this->getConsole()->readLine($this->maxLength);
        } while (!$this->allowEmpty && !$line);

        return $this->lastResponse = $line;
    }

    /**
     * @param  bool $allowEmpty
     */
    public function setAllowEmpty($allowEmpty)
    {
        $this->allowEmpty = $allowEmpty;
    }

    /**
     * @return bool
     */
    public function getAllowEmpty()
    {
        return $this->allowEmpty;
    }

    /**
     * @param int $maxLength
     */
    public function setMaxLength($maxLength)
    {
        $this->maxLength = $maxLength;
    }

    /**
     * @return int
     */
    public function getMaxLength()
    {
        return $this->maxLength;
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
}
