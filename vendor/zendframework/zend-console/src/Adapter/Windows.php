<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Console\Adapter;

use Zend\Console\Charset;
use Zend\Console\Exception;

class Windows extends Virtual
{
    /**
     * Whether or not mbstring is enabled
     *
     * @var null|bool
     */
    protected static $hasMBString;

    /**
     * Results of probing system capabilities
     *
     * @var mixed
     */
    protected $probeResult;

    /**
     * Results of mode command
     *
     * @var mixed
     */
    protected $modeResult;

    /**
     * Determine and return current console width.
     *
     * @return int
     */
    public function getWidth()
    {
        static $width;
        if ($width > 0) {
            return $width;
        }

        // Try to read console size from "mode" command
        if ($this->probeResult === null) {
            $this->runProbeCommand();
        }

        if (count($this->probeResult) && (int) $this->probeResult[0]) {
            $width = (int) $this->probeResult[0];
        } else {
            $width = parent::getWidth();
        }

        return $width;
    }

    /**
     * Determine and return current console height.
     *
     * @return int
     */
    public function getHeight()
    {
        static $height;
        if ($height > 0) {
            return $height;
        }

        // Try to read console size from "mode" command
        if ($this->probeResult === null) {
            $this->runProbeCommand();
        }

        if (count($this->probeResult) && (int) $this->probeResult[1]) {
            $height = (int) $this->probeResult[1];
        } else {
            $height = parent::getheight();
        }

        return $height;
    }

    /**
     * Probe for system capabilities and cache results
     *
     * Run a Windows Powershell command that determines parameters of console window. The command is fed through
     * standard input (with echo) to prevent Powershell from creating a sub-thread and hanging PHP when run through
     * a debugger/IDE.
     *
     * @return void
     */
    protected function runProbeCommand()
    {
        exec(
            'echo $size = $Host.ui.rawui.windowsize; write $($size.width) $($size.height) | powershell -NonInteractive -NoProfile -NoLogo -OutputFormat Text -Command -',
            $output,
            $return
        );
        if ($return || empty($output)) {
            $this->probeResult = '';
        } else {
            $this->probeResult = $output;
        }
    }

    /**
     * Run and cache results of mode command
     *
     * @return void
     */
    protected function runModeCommand()
    {
        exec('mode', $output, $return);
        if ($return || !count($output)) {
            $this->modeResult = '';
        } else {
            $this->modeResult = trim(implode('', $output));
        }
    }

    /**
     * Check if console is UTF-8 compatible
     *
     * @return bool
     */
    public function isUtf8()
    {
        // Try to read code page info from "mode" command
        if ($this->modeResult === null) {
            $this->runModeCommand();
        }

        if (preg_match('/Code page\:\s+(\d+)/', $this->modeResult, $matches)) {
            return (int) $matches[1] == 65001;
        }

        return false;
    }

    /**
     * Return current console window title.
     *
     * @return string
     */
    public function getTitle()
    {
        // Try to use powershell to retrieve console window title
        exec('powershell -command "write $Host.UI.RawUI.WindowTitle"', $output, $result);
        if ($result || !$output) {
            return '';
        }

        return trim($output, "\r\n");
    }

    /**
     * Set Console charset to use.
     *
     * @param Charset\CharsetInterface $charset
     */
    public function setCharset(Charset\CharsetInterface $charset)
    {
        $this->charset = $charset;
    }

    /**
     * Get charset currently in use by this adapter.
     *
     * @return Charset\CharsetInterface $charset
     */
    public function getCharset()
    {
        if ($this->charset === null) {
            $this->charset = $this->getDefaultCharset();
        }

        return $this->charset;
    }

    /**
     * @return Charset\AsciiExtended
     */
    public function getDefaultCharset()
    {
        return new Charset\AsciiExtended;
    }

    /**
     * Switch to utf-8 encoding
     *
     * @return void
     */
    protected function switchToUtf8()
    {
        shell_exec('mode con cp select=65001');
    }

    /**
     * Clear console screen
     */
    public function clear()
    {
        // Attempt to clear the screen using PowerShell command
        exec("powershell -NonInteractive -NoProfile -NoLogo -OutputFormat Text -Command Clear-Host", $output, $return);

        if ($return) {
            // Could not run powershell... fall back to filling the buffer with newlines
            echo str_repeat("\r\n", $this->getHeight());
        }
    }

    /**
     * Clear line at cursor position
     */
    public function clearLine()
    {
        echo "\r" . str_repeat(' ', $this->getWidth()) . "\r";
    }

    /**
     * Read a single character from the console input
     *
     * @param  string|null $mask A list of allowed chars
     * @throws Exception\RuntimeException
     * @return string
     */
    public function readChar($mask = null)
    {
        // Decide if we can use `choice` tool
        $useChoice = $mask !== null && preg_match('/^[a-zA-Z0-9]+$/D', $mask);

        if ($useChoice) {
            // Use Windows 95+ "choice" command, which allows for reading a
            // single character matching a mask, but is limited to lower ASCII
            // range.
            do {
                exec('choice /n /cs /c:' . $mask, $output, $return);
                if ($return == 255 || $return < 1 || $return > strlen($mask)) {
                    throw new Exception\RuntimeException('"choice" command failed to run. Are you using Windows XP or newer?');
                }

                // Fetch the char from mask
                $char = substr($mask, $return - 1, 1);
            } while ("" === $char || ($mask !== null && false === strstr($mask, $char)));

            return $char;
        }

        // Try to use PowerShell, giving it console access. Because PowersShell
        // interpreter can take a short while to load, we are emptying the
        // whole keyboard buffer and picking the last key that has been pressed
        // before or after PowerShell command has started. The ASCII code for
        // that key is then converted to a character.
        if ($mask === null) {
            exec(
                'powershell -NonInteractive -NoProfile -NoLogo -OutputFormat Text -Command "'
                . 'while ($Host.UI.RawUI.KeyAvailable) {$key = $Host.UI.RawUI.ReadKey(\'NoEcho,IncludeKeyDown\');}'
                . 'write $key.VirtualKeyCode;'
                . '"',
                $result,
                $return
            );

            // Retrieve char from the result.
            $char = !empty($result) ? implode('', $result) : null;

            if (!empty($char) && !$return) {
                // We have obtained an ASCII code, convert back to a char ...
                $char = chr($char);

                // ... and return it...
                return $char;
            }
        } else {
            // Windows and DOS will return carriage-return char (ASCII 13) when
            // the user presses [ENTER] key, but Console Adapter user might
            // have provided a \n Newline (ASCII 10) in the mask, to allow [ENTER].
            // We are going to replace all CR with NL to conform.
            $mask = strtr($mask, "\n", "\r");

            // Prepare a list of ASCII codes from mask chars
            $asciiMask = array_map(function ($char) {
                return ord($char);
            }, str_split($mask));
            $asciiMask = array_unique($asciiMask);

            // Char mask filtering is now handled by the PowerShell itself,
            // because it's a much faster method than invoking PS interpreter
            // after each mismatch. The command should return ASCII code of a
            // matching key.
            $result = $return = null;

            exec(
                'powershell -NonInteractive -NoProfile -NoLogo -OutputFormat Text -Command "'
                . '[int[]] $mask = ' . implode(',', $asciiMask) . ';'
                . 'do {'
                    . '$key = $Host.UI.RawUI.ReadKey(\'NoEcho,IncludeKeyDown\').VirtualKeyCode;'
                . '} while ( !($mask -contains $key) );'
                . 'write $key;'
                . '"',
                $result,
                $return
            );

            $char = !empty($result) ? trim(implode('', $result)) : null;

            if (!$return && $char && ($mask === null || in_array($char, $asciiMask))) {
                // Normalize CR to LF
                if ($char == 13) {
                    $char = 10;
                }

                // Convert to a char
                $char = chr($char);

                // ... and return it...
                return $char;
            }
        }

        // Fall back to standard input, which on Windows does not allow reading
        // a single character. This is a limitation of Windows streams
        // implementation (not PHP) and this behavior cannot be changed with a
        // command like "stty", known to POSIX systems.
        $stream = fopen('php://stdin', 'rb');
        do {
            $char = fgetc($stream);
            $char = substr(trim($char), 0, 1);
        } while (!$char || ($mask !== null && !stristr($mask, $char)));
        fclose($stream);

        return $char;
    }

    /**
     * Read a single line from the console input.
     *
     * @param  int $maxLength Maximum response length
     * @return string
     */
    public function readLine($maxLength = 2048)
    {
        $f    = fopen('php://stdin', 'r');
        $line = rtrim(fread($f, $maxLength), "\r\n");
        fclose($f);

        return $line;
    }
}
