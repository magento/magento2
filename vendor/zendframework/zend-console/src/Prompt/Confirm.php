<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Prompt;

class Confirm extends Char
{
    /**
     * @var string
     */
    protected $promptText = 'Are you sure?';

    /**
     * @var string
     */
    protected $allowedChars = 'yn';

    /**
     * @var string
     */
    protected $yesChar = 'y';

    /**
     * @var string
     */
    protected $noChar = 'n';

    /**
     * @var bool
     */
    protected $ignoreCase = true;

    /**
     * Ask the user for a single key stroke
     *
     * @param string    $promptText     The prompt text to display in console
     * @param string    $yesChar        The "yes" key (defaults to Y)
     * @param string    $noChar         The "no" key (defaults to N)
     */
    public function __construct(
        $promptText = 'Are you sure?',
        $yesChar = 'y',
        $noChar = 'n'
    ) {
        if ($promptText !== null) {
            $this->setPromptText($promptText);
        }

        if ($yesChar !== null) {
            $this->setYesChar($yesChar);
        }

        if ($noChar !== null) {
            $this->setNoChar($noChar);
        }
    }

    /**
     * Show the confirmation message and return result.
     *
     * @return bool
     */
    public function show()
    {
        $char = parent::show();
        if ($this->ignoreCase) {
            $response = strtolower($char) === strtolower($this->yesChar);
        } else {
            $response = $char === $this->yesChar;
        }
        return $this->lastResponse = $response;
    }

    /**
     * @param string $noChar
     */
    public function setNoChar($noChar)
    {
        $this->noChar = $noChar;
        $this->setAllowedChars($this->yesChar . $this->noChar);
    }

    /**
     * @return string
     */
    public function getNoChar()
    {
        return $this->noChar;
    }

    /**
     * @param string $yesChar
     */
    public function setYesChar($yesChar)
    {
        $this->yesChar = $yesChar;
        $this->setAllowedChars($this->yesChar . $this->noChar);
    }

    /**
     * @return string
     */
    public function getYesChar()
    {
        return $this->yesChar;
    }
}
