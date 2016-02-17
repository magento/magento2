<?php
/*
 * This file is part of the JShrink package.
 *
 * (c) Robert Hafner <tedivm@tedivm.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * JShrink
 *
 *
 * @package    JShrink
 * @author     Robert Hafner <tedivm@tedivm.com>
 */

namespace JShrink;

/**
 * Minifier
 *
 * Usage - Minifier::minify($js);
 * Usage - Minifier::minify($js, $options);
 * Usage - Minifier::minify($js, array('flaggedComments' => false));
 *
 * @package JShrink
 * @author Robert Hafner <tedivm@tedivm.com>
 * @license http://www.opensource.org/licenses/bsd-license.php  BSD License
 */
class Minifier
{
    /**
     * The input javascript to be minified.
     *
     * @var string
     */
    protected $input;

    /**
     * The location of the character (in the input string) that is next to be
     * processed.
     *
     * @var int
     */
    protected $index = 0;

    /**
     * The first of the characters currently being looked at.
     *
     * @var string
     */
    protected $a = '';

    /**
     * The next character being looked at (after a);
     *
     * @var string
     */
    protected $b = '';

    /**
     * This character is only active when certain look ahead actions take place.
     *
     *  @var string
     */
    protected $c;

    /**
     * Contains the options for the current minification process.
     *
     * @var array
     */
    protected $options;

    /**
     * Contains the default options for minification. This array is merged with
     * the one passed in by the user to create the request specific set of
     * options (stored in the $options attribute).
     *
     * @var array
     */
    protected static $defaultOptions = array('flaggedComments' => true);

    /**
     * Contains lock ids which are used to replace certain code patterns and
     * prevent them from being minified
     *
     * @var array
     */
    protected $locks = array();

    /**
     * Takes a string containing javascript and removes unneeded characters in
     * order to shrink the code without altering it's functionality.
     *
     * @param  string      $js      The raw javascript to be minified
     * @param  array       $options Various runtime options in an associative array
     * @throws \Exception
     * @return bool|string
     */
    public static function minify($js, $options = array())
    {
        try {
            ob_start();

            $jshrink = new Minifier();
            $js = $jshrink->lock($js);
            $jshrink->minifyDirectToOutput($js, $options);

            // Sometimes there's a leading new line, so we trim that out here.
            $js = ltrim(ob_get_clean());
            $js = $jshrink->unlock($js);
            unset($jshrink);

            return $js;

        } catch (\Exception $e) {

            if (isset($jshrink)) {
                // Since the breakdownScript function probably wasn't finished
                // we clean it out before discarding it.
                $jshrink->clean();
                unset($jshrink);
            }

            // without this call things get weird, with partially outputted js.
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Processes a javascript string and outputs only the required characters,
     * stripping out all unneeded characters.
     *
     * @param string $js      The raw javascript to be minified
     * @param array  $options Various runtime options in an associative array
     */
    protected function minifyDirectToOutput($js, $options)
    {
        $this->initialize($js, $options);
        $this->loop();
        $this->clean();
    }

    /**
     *  Initializes internal variables, normalizes new lines,
     *
     * @param string $js      The raw javascript to be minified
     * @param array  $options Various runtime options in an associative array
     */
    protected function initialize($js, $options)
    {
        $this->options = array_merge(static::$defaultOptions, $options);
        $js = str_replace("\r\n", "\n", $js);
        $js = str_replace('/**/', '', $js);
        $this->input = str_replace("\r", "\n", $js);

        // We add a newline to the end of the script to make it easier to deal
        // with comments at the bottom of the script- this prevents the unclosed
        // comment error that can otherwise occur.
        $this->input .= PHP_EOL;

        // Populate "a" with a new line, "b" with the first character, before
        // entering the loop
        $this->a = "\n";
        $this->b = $this->getReal();
    }

    /**
     * The primary action occurs here. This function loops through the input string,
     * outputting anything that's relevant and discarding anything that is not.
     */
    protected function loop()
    {
        while ($this->a !== false && !is_null($this->a) && $this->a !== '') {

            switch ($this->a) {
                // new lines
                case "\n":
                    // if the next line is something that can't stand alone preserve the newline
                    if (strpos('(-+{[@', $this->b) !== false) {
                        echo $this->a;
                        $this->saveString();
                        break;
                    }

                    // if B is a space we skip the rest of the switch block and go down to the
                    // string/regex check below, resetting $this->b with getReal
                    if($this->b === ' ')
                        break;

                // otherwise we treat the newline like a space

                case ' ':
                    if(static::isAlphaNumeric($this->b))
                        echo $this->a;

                    $this->saveString();
                    break;

                default:
                    switch ($this->b) {
                        case "\n":
                            if (strpos('}])+-"\'', $this->a) !== false) {
                                echo $this->a;
                                $this->saveString();
                                break;
                            } else {
                                if (static::isAlphaNumeric($this->a)) {
                                    echo $this->a;
                                    $this->saveString();
                                }
                            }
                            break;

                        case ' ':
                            if(!static::isAlphaNumeric($this->a))
                                break;

                        default:
                            // check for some regex that breaks stuff
                            if ($this->a == '/' && ($this->b == '\'' || $this->b == '"')) {
                                $this->saveRegex();
                                continue;
                            }

                            echo $this->a;
                            $this->saveString();
                            break;
                    }
            }

            // do reg check of doom
            $this->b = $this->getReal();

            if(($this->b == '/' && strpos('(,=:[!&|?', $this->a) !== false))
                $this->saveRegex();
        }
    }

    /**
     * Resets attributes that do not need to be stored between requests so that
     * the next request is ready to go. Another reason for this is to make sure
     * the variables are cleared and are not taking up memory.
     */
    protected function clean()
    {
        unset($this->input);
        $this->index = 0;
        $this->a = $this->b = '';
        unset($this->c);
        unset($this->options);
    }

    /**
     * Returns the next string for processing based off of the current index.
     *
     * @return string
     */
    protected function getChar()
    {
        // Check to see if we had anything in the look ahead buffer and use that.
        if (isset($this->c)) {
            $char = $this->c;
            unset($this->c);

        // Otherwise we start pulling from the input.
        } else {
            $char = substr($this->input, $this->index, 1);

            // If the next character doesn't exist return false.
            if (isset($char) && $char === false) {
                return false;
            }

            // Otherwise increment the pointer and use this char.
            $this->index++;
        }

        // Normalize all whitespace except for the newline character into a
        // standard space.
        if($char !== "\n" && ord($char) < 32)

            return ' ';

        return $char;
    }

    /**
     * This function gets the next "real" character. It is essentially a wrapper
     * around the getChar function that skips comments. This has significant
     * performance benefits as the skipping is done using native functions (ie,
     * c code) rather than in script php.
     *
     *
     * @return string            Next 'real' character to be processed.
     * @throws \RuntimeException
     */
    protected function getReal()
    {
        $startIndex = $this->index;
        $char = $this->getChar();

        // Check to see if we're potentially in a comment
        if ($char !== '/') {
            return $char;
        }

        $this->c = $this->getChar();

        if ($this->c == '/') {
            return $this->processOneLineComments($startIndex);

        } elseif ($this->c == '*') {
            return $this->processMultiLineComments($startIndex);
        }

        return $char;
    }

    /**
     * Removed one line comments, with the exception of some very specific types of
     * conditional comments.
     *
     * @param  int    $startIndex The index point where "getReal" function started
     * @return string
     */
    protected function processOneLineComments($startIndex)
    {
        $thirdCommentString = substr($this->input, $this->index, 1);

        // kill rest of line
        $this->getNext("\n");

        if ($thirdCommentString == '@') {
            $endPoint = ($this->index) - $startIndex;
            unset($this->c);
            $char = "\n" . substr($this->input, $startIndex, $endPoint);
        } else {
            // first one is contents of $this->c
            $this->getChar();
            $char = $this->getChar();
        }

        return $char;
    }

    /**
     * Skips multiline comments where appropriate, and includes them where needed.
     * Conditional comments and "license" style blocks are preserved.
     *
     * @param  int               $startIndex The index point where "getReal" function started
     * @return bool|string       False if there's no character
     * @throws \RuntimeException Unclosed comments will throw an error
     */
    protected function processMultiLineComments($startIndex)
    {
        $this->getChar(); // current C
        $thirdCommentString = $this->getChar();

        // kill everything up to the next */ if it's there
        if ($this->getNext('*/')) {

            $this->getChar(); // get *
            $this->getChar(); // get /
            $char = $this->getChar(); // get next real character

            // Now we reinsert conditional comments and YUI-style licensing comments
            if (($this->options['flaggedComments'] && $thirdCommentString == '!')
                || ($thirdCommentString == '@') ) {

                // If conditional comments or flagged comments are not the first thing in the script
                // we need to echo a and fill it with a space before moving on.
                if ($startIndex > 0) {
                    echo $this->a;
                    $this->a = " ";

                    // If the comment started on a new line we let it stay on the new line
                    if ($this->input[($startIndex - 1)] == "\n") {
                        echo "\n";
                    }
                }

                $endPoint = ($this->index - 1) - $startIndex;
                echo substr($this->input, $startIndex, $endPoint);

                return $char;
            }

        } else {
            $char = false;
        }

        if($char === false)
            throw new \RuntimeException('Unclosed multiline comment at position: ' . ($this->index - 2));

        // if we're here c is part of the comment and therefore tossed
        if(isset($this->c))
            unset($this->c);

        return $char;
    }

    /**
     * Pushes the index ahead to the next instance of the supplied string. If it
     * is found the first character of the string is returned and the index is set
     * to it's position.
     *
     * @param  string       $string
     * @return string|false Returns the first character of the string or false.
     */
    protected function getNext($string)
    {
        // Find the next occurrence of "string" after the current position.
        $pos = strpos($this->input, $string, $this->index);

        // If it's not there return false.
        if($pos === false)

            return false;

        // Adjust position of index to jump ahead to the asked for string
        $this->index = $pos;

        // Return the first character of that string.
        return substr($this->input, $this->index, 1);
    }

    /**
     * When a javascript string is detected this function crawls for the end of
     * it and saves the whole string.
     *
     * @throws \RuntimeException Unclosed strings will throw an error
     */
    protected function saveString()
    {
        $startpos = $this->index;

        // saveString is always called after a gets cleared, so we push b into
        // that spot.
        $this->a = $this->b;

        // If this isn't a string we don't need to do anything.
        if ($this->a != "'" && $this->a != '"') {
            return;
        }

        // String type is the quote used, " or '
        $stringType = $this->a;

        // Echo out that starting quote
        echo $this->a;

        // Loop until the string is done
        while (1) {

            // Grab the very next character and load it into a
            $this->a = $this->getChar();

            switch ($this->a) {

                // If the string opener (single or double quote) is used
                // output it and break out of the while loop-
                // The string is finished!
                case $stringType:
                    break 2;

                // New lines in strings without line delimiters are bad- actual
                // new lines will be represented by the string \n and not the actual
                // character, so those will be treated just fine using the switch
                // block below.
                case "\n":
                    throw new \RuntimeException('Unclosed string at position: ' . $startpos );
                    break;

                // Escaped characters get picked up here. If it's an escaped new line it's not really needed
                case '\\':

                    // a is a slash. We want to keep it, and the next character,
                    // unless it's a new line. New lines as actual strings will be
                    // preserved, but escaped new lines should be reduced.
                    $this->b = $this->getChar();

                    // If b is a new line we discard a and b and restart the loop.
                    if ($this->b == "\n") {
                        break;
                    }

                    // echo out the escaped character and restart the loop.
                    echo $this->a . $this->b;
                    break;


                // Since we're not dealing with any special cases we simply
                // output the character and continue our loop.
                default:
                    echo $this->a;
            }
        }
    }

    /**
     * When a regular expression is detected this function crawls for the end of
     * it and saves the whole regex.
     *
     * @throws \RuntimeException Unclosed regex will throw an error
     */
    protected function saveRegex()
    {
        echo $this->a . $this->b;

        while (($this->a = $this->getChar()) !== false) {
            if($this->a == '/')
                break;

            if ($this->a == '\\') {
                echo $this->a;
                $this->a = $this->getChar();
            }

            if($this->a == "\n")
                throw new \RuntimeException('Unclosed regex pattern at position: ' . $this->index);

            echo $this->a;
        }
        $this->b = $this->getReal();
    }

    /**
     * Checks to see if a character is alphanumeric.
     *
     * @param  string $char Just one character
     * @return bool
     */
    protected static function isAlphaNumeric($char)
    {
        return preg_match('/^[\w\$]$/', $char) === 1 || $char == '/';
    }

    /**
     * Replace patterns in the given string and store the replacement
     *
     * @param  string $js The string to lock
     * @return bool
     */
    protected function lock($js)
    {
        /* lock things like <code>"asd" + ++x;</code> */
        $lock = '"LOCK---' . crc32(time()) . '"';

        $matches = array();
        preg_match('/([+-])(\s+)([+-])/', $js, $matches);
        if (empty($matches)) {
            return $js;
        }

        $this->locks[$lock] = $matches[2];

        $js = preg_replace('/([+-])\s+([+-])/', "$1{$lock}$2", $js);
        /* -- */

        return $js;
    }

    /**
     * Replace "locks" with the original characters
     *
     * @param  string $js The string to unlock
     * @return bool
     */
    protected function unlock($js)
    {
        if (!count($this->locks)) {
            return $js;
        }

        foreach ($this->locks as $lock => $replacement) {
            $js = str_replace($lock, $replacement, $js);
        }

        return $js;
    }

}
