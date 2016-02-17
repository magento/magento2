<?php

namespace League\CLImate;

use League\CLImate\Decorator\Style;
use League\CLImate\Settings\Manager;
use League\CLImate\TerminalObject\Router\Router;
use League\CLImate\Util\Output;
use League\CLImate\Util\UtilFactory;

/**
 * @method mixed black(string $str = null)
 * @method mixed red(string $str = null)
 * @method mixed green(string $str = null)
 * @method mixed yellow(string $str = null)
 * @method mixed blue(string $str = null)
 * @method mixed magenta(string $str = null)
 * @method mixed cyan(string $str = null)
 * @method mixed lightGray(string $str = null)
 * @method mixed darkGray(string $str = null)
 * @method mixed lightRed(string $str = null)
 * @method mixed lightGreen(string $str = null)
 * @method mixed lightYellow(string $str = null)
 * @method mixed lightBlue(string $str = null)
 * @method mixed lightMagenta(string $str = null)
 * @method mixed lightCyan(string $str = null)
 * @method mixed white(string $str = null)
 *
 * @method mixed backgroundBlack(string $str = null)
 * @method mixed backgroundRed(string $str = null)
 * @method mixed backgroundGreen(string $str = null)
 * @method mixed backgroundYellow(string $str = null)
 * @method mixed backgroundBlue(string $str = null)
 * @method mixed backgroundMagenta(string $str = null)
 * @method mixed backgroundCyan(string $str = null)
 * @method mixed backgroundLightGray(string $str = null)
 * @method mixed backgroundDarkGray(string $str = null)
 * @method mixed backgroundLightRed(string $str = null)
 * @method mixed backgroundLightGreen(string $str = null)
 * @method mixed backgroundLightYellow(string $str = null)
 * @method mixed backgroundLightBlue(string $str = null)
 * @method mixed backgroundLightMagenta(string $str = null)
 * @method mixed backgroundLightCyan(string $str = null)
 * @method mixed backgroundWhite(string $str = null)
 *
 * @method mixed bold(string $str = null)
 * @method mixed dim(string $str = null)
 * @method mixed underline(string $str = null)
 * @method mixed blink(string $str = null)
 * @method mixed invert(string $str = null)
 * @method mixed hidden(string $str = null)
 *
 * @method mixed info(string $str = null)
 * @method mixed comment(string $str = null)
 * @method mixed whisper(string $str = null)
 * @method mixed shout(string $str = null)
 * @method mixed error(string $str = null)
 *
 * @method mixed out(string $str)
 * @method mixed table(array $data)
 * @method mixed json(mixed $var)
 * @method mixed br($count = 1)
 * @method mixed tab($count = 1)
 * @method mixed draw(string $art)
 * @method mixed border(string $char = null, integer $length = null)
 * @method mixed dump(mixed $var)
 * @method mixed flank(string $output, string $char = null, integer $length = null)
 * @method mixed progress(integer $total = null)
 * @method mixed padding(integer $length = 0, string $char = '.'')
 * @method mixed input(string $prompt, Reader $reader = null)
 * @method mixed confirm(string $prompt, Reader $reader = null)
 * @method mixed columns(array $data, $column_count = null)
 * @method mixed clear()
 *
 * @method \League\CLImate\CLImate addArt(string $dir)
 */
class CLImate
{
    /**
     * An instance of the Style class
     *
     * @var \League\CLImate\Decorator\Style $style
     */
    public $style;

    /**
     * An instance of the Terminal Object Router class
     *
     * @var \League\CLImate\TerminalObject\Router\Router $router
     */
    protected $router;

    /**
     * An instance of the Settings Manager class
     *
     * @var \League\CLImate\Settings\Manager $settings
     */
    protected $settings;

    /**
     * An instance of the Output class
     *
     * @var \League\CLImate\Util\Output $output
     */
    protected $output;

    /**
     * An instance of the Util Factory
     *
     * @var \League\CLImate\Util\UtilFactory $util
     */
    protected $util;

    public function __construct()
    {
        $this->setStyle();
        $this->setRouter();
        $this->setSettingsManager();
        $this->setOutput();
        $this->setUtil();
    }

    /**
     * Set the style property
     *
     * @param \League\CLImate\Decorator\Style $style
     */
    public function setStyle(Style $style = null)
    {
        $this->style = $style ?: new Style();
    }

    /**
     * Set the router property
     *
     * @param \League\CLImate\TerminalObject\Router\Router $router
     */
    public function setRouter(Router $router = null)
    {
        $this->router = $router ?: new Router();
    }

    /**
     * Set the settings property
     *
     * @param \League\CLImate\Settings\Manager $manager
     */
    public function setSettingsManager(Manager $manager = null)
    {
        $this->settings = $manager ?: new Manager();
    }

    /**
     * Set the output property
     *
     * @param \League\CLImate\Util\Output $output
     */
    public function setOutput(Output $output = null)
    {
        $this->output = $output ?: new Output();
    }

    /**
     * Set the util property
     *
     * @param \League\CLImate\Util\UtilFactory $util
     */
    public function setUtil(UtilFactory $util = null)
    {
        $this->util = $util ?: new UtilFactory();
    }

    /**
     * Force ansi support on
     *
     * @return \League\CLImate\CLImate
     */
    public function forceAnsiOn()
    {
        $this->util->system->forceAnsiOn();

        return $this;
    }

    /**
     * Force ansi support off
     *
     * @return \League\CLImate\CLImate
     */
    public function forceAnsiOff()
    {
        $this->util->system->forceAnsiOff();

        return $this;
    }

    /**
     * Check if we have valid output
     *
     * @param  mixed   $output
     *
     * @return boolean
     */
    protected function hasOutput($output)
    {
        if (!empty($output)) {
            return true;
        }

        // Check for type first to avoid errors with objects/arrays/etc
        return ((is_string($output) || is_numeric($output)) && strlen($output) > 0);
    }

    /**
     * Search for the method within the string
     * and route it if we find one.
     *
     * @param  string $method
     * @param  string $name
     *
     * @return string The new string without the executed method.
     */
    protected function parseStyleMethod($method, $name)
    {
        // If the name starts with this method string...
        if (substr($name, 0, strlen($method)) == $method) {
            // ...remove the method name from the beginning of the string...
            $name = substr($name, strlen($method));

            // ...and trim off any of those underscores hanging around
            $name = ltrim($name, '_');

            $this->style->set($method);
        }

        return $name;
    }

    /**
     * Search for any style methods within the name and apply them
     *
     * @param  string $name
     * @param  array $method_search
     *
     * @return string Anything left over after applying styles
     */
    protected function applyStyleMethods($name, $method_search = null)
    {
        // Get all of the possible style attributes
        $method_search = $method_search ?: array_keys($this->style->all());

        $new_name = $this->searchForStyleMethods($name, $method_search);

        // While we still have a name left and we keep finding methods,
        // loop through the possibilities
        if (strlen($new_name) > 0 && $new_name != $name) {
            return $this->applyStyleMethods($new_name, $method_search);
        }

        return $new_name;
    }

    /**
     * Search for style methods in the current name
     *
     * @param string $name
     * @param array $search
     */
    protected function searchForStyleMethods($name, $search)
    {
        // Loop through the possible methods
        foreach ($search as $method) {
            // See if we found a valid method
            $name = $this->parseStyleMethod($method, $name);
        }

        return $name;
    }

    /**
     * Build up the terminal object and return it
     *
     * @param string $name
     * @param array $arguments
     *
     * @return object|null
     */
    protected function buildTerminalObject($name, $arguments)
    {
        // Retrieve the parser for the current set of styles
        $parser = $this->style->parser($this->util->system);

        // Reset the styles
        $this->style->reset();

        // Execute the terminal object
        $this->router->settings($this->settings);
        $this->router->parser($parser);
        $this->router->output($this->output);
        $this->router->util($this->util);

        return $this->router->execute($name, $arguments);
    }

    /**
     * Route anything leftover after styles were applied
     *
     * @param string $name
     * @param array $arguments
     *
     * @return object|null
     */
    protected function routeRemainingMethod($name, array $arguments)
    {
        // If we still have something left, let's figure out what it is
        if ($this->router->exists($name)) {
            $obj = $this->buildTerminalObject($name, $arguments);

            // If something was returned, return it
            if (is_object($obj)) {
                return $obj;
            }
        } elseif ($this->settings->exists($name)) {
            $this->settings->add($name, reset($arguments));
        } else {
            // If we can't find it at this point, let's fail gracefully
            $this->out(reset($arguments));
        }
    }

    /**
     * Magic method for anything called that doesn't exist
     *
     * @param string $requested_method
     * @param array  $arguments
     *
     * List of many of the possible method being called here
     * documented at the top of this class.
     */
    public function __call($requested_method, $arguments)
    {
        // Convert to snake case
        $name   = strtolower(preg_replace('/(.)([A-Z])/', '$1_$2', $requested_method));

        // The first argument is the string|array|object we want to echo out
        $output = reset($arguments);

        $name   = $this->applyStyleMethods($name);

        if (strlen($name)) {
            // If we have something left, let's try and route it to the appropriate place
            $result = $this->routeRemainingMethod($name, $arguments);
            if ($result) {
                return $result;
            }
        } elseif ($this->hasOutput($output)) {
            // If we have fulfilled all of the requested methods and we have output, output it
            $this->out($output);
        }

        return $this;
    }
}
