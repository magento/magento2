<?php

namespace League\CLImate\TerminalObject\Dynamic;

use League\CLImate\Util\Reader\ReaderInterface;
use League\CLImate\Util\Reader\Stdin;

class Input extends DynamicTerminalObject
{
    /**
     * The prompt text
     *
     * @var string $prompt
     */
    protected $prompt;

    /**
     * An array of acceptable responses
     *
     * @var array|object $acceptable
     */
    protected $acceptable;

    /**
     * Whether we should be strict about the response given
     *
     * @var boolean $strict
     */
    protected $strict = false;

    /**
     * Whether we should display the acceptable responses to the user
     *
     * @var boolean $show_acceptable
     */
    protected $show_acceptable = false;

    /**
     * A default answer in the case of no user response,
     * prevents re-prompting
     *
     * @var string
     */
    protected $default = '';

    /**
     * An instance of ReaderInterface
     *
     * @var \League\CLImate\Util\Reader\ReaderInterface $reader
     */
    protected $reader;

    public function __construct($prompt, ReaderInterface $reader = null)
    {
        $this->prompt = $prompt;
        $this->reader = $reader ?: new Stdin();
    }

    /**
     * Do it! Prompt the user for information!
     *
     * @return string
     */
    public function prompt()
    {
        $prompt_str = $this->parser->apply($this->promptFormatted());

        $this->output->sameLine()->write($prompt_str);

        $response = $this->reader->line();

        if (strlen($response) == 0 && strlen($this->default)) {
            $response = $this->default;
        }

        if ($this->isValidResponse($response)) {
            return $response;
        }

        return $this->prompt();
    }

    /**
     * Define the acceptable responses and whether or not to
     * display them to the user
     *
     * @param  array|object $acceptable
     * @param  boolean $show
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    public function accept($acceptable, $show = false)
    {
        $this->acceptable      = $acceptable;
        $this->show_acceptable = $show;

        return $this;
    }

    /**
     * Define whether we should be strict about exact responses
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    public function strict()
    {
        $this->strict = true;

        return $this;
    }

    /**
     * Set a default response
     *
     * @param string $default
     *
     * @return \League\CLImate\TerminalObject\Dynamic\Input
     */
    public function defaultTo($default)
    {
        $this->default = $default;

        return $this;
    }

    /**
     * Format the acceptable responses as options
     *
     * @return string
     */
    protected function acceptableFormatted()
    {
        if (!is_array($this->acceptable)) {
            return '';
        }

        return '[' . implode('/', $this->acceptable) . ']';
    }

    /**
     * Format the prompt incorporating spacing and any acceptable options
     *
     * @return string
     */
    protected function promptFormatted()
    {
        $prompt = $this->prompt . ' ';

        if ($this->show_acceptable) {
            $prompt .= $this->acceptableFormatted() . ' ';
        }

        return $prompt;
    }

    /**
     * Apply some string manipulation functions for normalization
     *
     * @param string|array $var
     */
    protected function levelPlayingField($var)
    {
        $levelers = ['trim', 'strtolower'];

        foreach ($levelers as $leveler) {
            if (is_array($var)) {
                $var = array_map($leveler, $var);
            } else {
                $var = $leveler($var);
            }
        }

        return $var;
    }

    /**
     * Determine whether or not the acceptable property is of type closure
     *
     * @return boolean
     */
    protected function acceptableIsClosure()
    {
        return (is_object($this->acceptable) && $this->acceptable instanceof \Closure);
    }

    /**
     * Determine if the user's response is in the acceptable responses array
     *
     * @param string $response
     *
     * @return boolean $response
     */
    protected function isAcceptableResponse($response)
    {
        if (!$this->strict) {
            $this->acceptable = $this->levelPlayingField((array) $this->acceptable);
            $response         = $this->levelPlayingField($response);
        }

        return in_array($response, $this->acceptable);
    }

    /**
     * Determine if the user's response is valid based on the current settings
     *
     * @param string $response
     *
     * @return boolean $response
     */
    protected function isValidResponse($response)
    {
        if (empty($this->acceptable)) {
            return true;
        }

        if ($this->acceptableIsClosure()) {
            return call_user_func($this->acceptable, $response);
        }

        return $this->isAcceptableResponse($response);
    }
}
