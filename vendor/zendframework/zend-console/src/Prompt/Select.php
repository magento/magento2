<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Prompt;

use Zend\Console\Exception;

class Select extends Char
{
    /**
     * @var string
     */
    protected $promptText = 'Please select an option';

    /**
     * @var bool
     */
    protected $ignoreCase = true;

    /**
     * @var array
     */
    protected $options = array();

    /**
     * Ask the user to select one of pre-defined options
     *
     * @param string    $promptText     The prompt text to display in console
     * @param array     $options        Allowed options
     * @param bool      $allowEmpty     Allow empty (no) selection?
     * @param bool      $echo           True to display selected option?
     * @throws Exception\BadMethodCallException if no options available
     */
    public function __construct(
        $promptText = 'Please select one option',
        $options = array(),
        $allowEmpty = false,
        $echo = false
    ) {
        if ($promptText !== null) {
            $this->setPromptText($promptText);
        }

        if (!count($options)) {
            throw new Exception\BadMethodCallException(
                'Cannot construct a "select" prompt without any options'
            );
        }

        $this->setOptions($options);

        if ($allowEmpty !== null) {
            $this->setAllowEmpty($allowEmpty);
        }

        if ($echo !== null) {
            $this->setEcho($echo);
        }
    }

    /**
     * Show a list of options and prompt the user to select one of them.
     *
     * @return string       Selected option
     */
    public function show()
    {
        // Show prompt text and available options
        $console = $this->getConsole();
        $console->writeLine($this->promptText);
        foreach ($this->options as $k => $v) {
            $console->writeLine('  ' . $k . ') ' . $v);
        }

        //  Prepare mask
        $mask = implode("", array_keys($this->options));
        if ($this->allowEmpty) {
            $mask .= "\r\n";
        }

        // Prepare other params for parent class
        $this->setAllowedChars($mask);
        $oldPrompt        = $this->promptText;
        $oldEcho          = $this->echo;
        $this->echo       = false;
        $this->promptText = null;

        // Retrieve a single character
        $response = parent::show();

        // Restore old params
        $this->promptText = $oldPrompt;
        $this->echo       = $oldEcho;

        // Display selected option if echo is enabled
        if ($this->echo) {
            if (isset($this->options[$response])) {
                $console->writeLine($this->options[$response]);
            } else {
                $console->writeLine();
            }
        }

        $this->lastResponse = $response;
        return $response;
    }

    /**
     * Set allowed options
     *
     * @param array|\Traversable $options
     * @throws Exception\BadMethodCallException
     */
    public function setOptions($options)
    {
        if (!is_array($options) && !$options instanceof \Traversable) {
            throw new Exception\BadMethodCallException(
                'Please specify an array or Traversable object as options'
            );
        }

        if (!is_array($options)) {
            $this->options = array();
            foreach ($options as $k => $v) {
                $this->options[$k] = $v;
            }
        } else {
            $this->options = $options;
        }
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
