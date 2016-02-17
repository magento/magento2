<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category  Zend
 * @package   Zend_Text_Figlet
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id$
 */

/**
 * Zend_Text_Figlet is a PHP implementation of FIGlet
 *
 * @category  Zend
 * @package   Zend_Text_Figlet
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Text_Figlet
{
    /**
     * Smush2 layout modes
     */
    const SM_EQUAL     = 0x01;
    const SM_LOWLINE   = 0x02;
    const SM_HIERARCHY = 0x04;
    const SM_PAIR      = 0x08;
    const SM_BIGX      = 0x10;
    const SM_HARDBLANK = 0x20;
    const SM_KERN      = 0x40;
    const SM_SMUSH     = 0x80;

    /**
     * Smush mode override modes
     */
    const SMO_NO    = 0;
    const SMO_YES   = 1;
    const SMO_FORCE = 2;

    /**
     * Justifications
     */
    const JUSTIFICATION_LEFT   = 0;
    const JUSTIFICATION_CENTER = 1;
    const JUSTIFICATION_RIGHT  = 2;

    /**
     * Write directions
     */
    const DIRECTION_LEFT_TO_RIGHT = 0;
    const DIRECTION_RIGHT_TO_LEFT = 1;

    /**
     * Magic fontfile number
     */
    const FONTFILE_MAGIC_NUMBER = 'flf2';

    /**
     * Array containing all characters of the current font
     *
     * @var array
     */
    protected $_charList = array();

    /**
     * Indicates if a font was loaded yet
     *
     * @var boolean
     */
    protected $_fontLoaded = false;

    /**
     * Latin-1 codes for German letters, respectively:
     *
     * LATIN CAPITAL LETTER A WITH DIAERESIS = A-umlaut
     * LATIN CAPITAL LETTER O WITH DIAERESIS = O-umlaut
     * LATIN CAPITAL LETTER U WITH DIAERESIS = U-umlaut
     * LATIN SMALL LETTER A WITH DIAERESIS = a-umlaut
     * LATIN SMALL LETTER O WITH DIAERESIS = o-umlaut
     * LATIN SMALL LETTER U WITH DIAERESIS = u-umlaut
     * LATIN SMALL LETTER SHARP S = ess-zed
     *
     * @var array
     */
    protected $_germanChars = array(196, 214, 220, 228, 246, 252, 223);

    /**
     * Output width, defaults to 80.
     *
     * @var integer
     */
    protected $_outputWidth = 80;

    /**
     * Hard blank character
     *
     * @var string
     */
    protected $_hardBlank;

    /**
     * Height of the characters
     *
     * @var integer
     */
    protected $_charHeight;

    /**
     * Max length of any character
     *
     * @var integer
     */
    protected $_maxLength;

    /**
     * Smush mode
     *
     * @var integer
     */
    protected $_smushMode = 0;

    /**
     * Smush defined by the font
     *
     * @var integer
     */
    protected $_fontSmush = 0;

    /**
     * Smush defined by the user
     *
     * @var integer
     */
    protected $_userSmush = 0;

    /**
     * Wether to handle paragraphs || not
     *
     * @var boolean
     */
    protected $_handleParagraphs = false;

    /**
     * Justification for the text, according to $_outputWidth
     *
     * For using font default, this parameter should be null, else one of
     * the values of Zend_Text_Figlet::JUSTIFICATION_*
     *
     * @var integer
     */
    protected $_justification = null;

    /**
     * Direction of text-writing, namely right to left
     *
     * For using font default, this parameter should be null, else one of
     * the values of Zend_Text_Figlet::DIRECTION_*
     *
     * @var integer
     */
    protected $_rightToLeft = null;

    /**
     * Override font file smush layout
     *
     * @var integer
     */
    protected $_smushOverride = 0;

    /**
     * Options of the current font
     *
     * @var array
     */
    protected $_fontOptions = array();

    /**
     * Previous character width
     *
     * @var integer
     */
    protected $_previousCharWidth = 0;

    /**
     * Current character width
     *
     * @var integer
     */
    protected $_currentCharWidth = 0;

    /**
     * Current outline length
     *
     * @var integer
     */
    protected $_outlineLength = 0;

    /**
     * Maxmimum outline length
     *
     * @var integer
     */
    protected $_outlineLengthLimit = 0;

    /**
     * In character line
     *
     * @var string
     */
    protected $_inCharLine;

    /**
     * In character line length
     *
     * @var integer
     */
    protected $_inCharLineLength = 0;

    /**
     * Maximum in character line length
     *
     * @var integer
     */
    protected $_inCharLineLengthLimit = 0;

    /**
     * Current char
     *
     * @var array
     */
    protected $_currentChar = null;

    /**
     * Current output line
     *
     * @var array
     */
    protected $_outputLine;

    /**
     * Current output
     *
     * @var string
     */
    protected $_output;

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $_skipOptions = array(
        'options',
        'config',
    );

    /**
     * Instantiate the FIGlet with a specific font. If no font is given, the
     * standard font is used. You can also supply multiple options via
     * the $options variable, which can either be an array or an instance of
     * Zend_Config.
     *
     * @param array|Zend_Config $options Options for the output
     */
    public function __construct($options = null)
    {
        // Set options
        if (is_array($options)) {
            $this->setOptions($options);
        } else if ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        // If no font was defined, load default font
        if (!$this->_fontLoaded) {
            $this->_loadFont(dirname(__FILE__) . '/Figlet/zend-framework.flf');
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Zend_Text_Figlet
     * @return Zend_Text_Figlet
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->_skipOptions)) {
                continue;
            }

            $method = 'set' . ucfirst($key);
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set options from config object
     *
     * @param  Zend_Config $config Configuration for Zend_Text_Figlet
     * @return Zend_Text_Figlet
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set a font to use
     *
     * @param  string $font Path to the font
     * @return Zend_Text_Figlet
     */
    public function setFont($font)
    {
        $this->_loadFont($font);
        return $this;
    }

    /**
     * Set handling of paragraphs
     *
     * @param  boolean $handleParagraphs Wether to handle paragraphs or not
     * @return Zend_Text_Figlet
     */
    public function setHandleParagraphs($handleParagraphs)
    {
        $this->_handleParagraphs = (bool) $handleParagraphs;
        return $this;
    }

    /**
     * Set the justification. 0 stands for left aligned, 1 for centered and 2
     * for right aligned.
     *
     * @param  integer $justification Justification of the output text
     * @return Zend_Text_Figlet
     */
    public function setJustification($justification)
    {
        $this->_justification = min(3, max(0, (int) $justification));
        return $this;
    }

    /**
     * Set the output width
     *
     * @param  integer $outputWidth Output with which should be used for word
     *                              wrapping and justification
     * @return Zend_Text_Figlet
     */
    public function setOutputWidth($outputWidth)
    {
        $this->_outputWidth = max(1, (int) $outputWidth);
        return $this;
    }

    /**
     * Set right to left mode. For writing from left to right, use
     * Zend_Text_Figlet::DIRECTION_LEFT_TO_RIGHT. For writing from right to left,
     * use Zend_Text_Figlet::DIRECTION_RIGHT_TO_LEFT.
     *
     * @param  integer $rightToLeft Right-to-left mode
     * @return Zend_Text_Figlet
     */
    public function setRightToLeft($rightToLeft)
    {
        $this->_rightToLeft = min(1, max(0, (int) $rightToLeft));
        return $this;
    }

    /**
     * Set the smush mode.
     *
     * Use one of the constants of Zend_Text_Figlet::SM_*, you may combine them.
     *
     * @param  integer $smushMode Smush mode to use for generating text
     * @return Zend_Text_Figlet
     */
    public function setSmushMode($smushMode)
    {
        $smushMode = (int) $smushMode;

        if ($smushMode < -1) {
            $this->_smushOverride = self::SMO_NO;
        } else {
            if ($smushMode === 0) {
                $this->_userSmush = self::SM_KERN;
            } else if ($smushMode === -1) {
                $this->_userSmush = 0;
            } else {
                $this->_userSmush = (($smushMode & 63) | self::SM_SMUSH);
            }

            $this->_smushOverride = self::SMO_YES;
        }

        $this->_setUsedSmush();

        return $this;
    }

    /**
     * Render a FIGlet text
     *
     * @param  string $text     Text to convert to a figlet text
     * @param  string $encoding Encoding of the input string
     * @throws InvalidArgumentException When $text is not a string
     * @throws Zend_Text_Figlet_Exception    When $text it not properly encoded
     * @return string
     */
    public function render($text, $encoding = 'UTF-8')
    {
        if (!is_string($text)) {
            throw new InvalidArgumentException('$text must be a string');
        }

        if ($encoding !== 'UTF-8') {
            $text = iconv($encoding, 'UTF-8', $text);
        }

        $this->_output     = '';
        $this->_outputLine = array();

        $this->_clearLine();

        $this->_outlineLengthLimit    = ($this->_outputWidth - 1);
        $this->_inCharLineLengthLimit = ($this->_outputWidth * 4 + 100);

        $wordBreakMode  = 0;
        $lastCharWasEol = false;
        $textLength     = @iconv_strlen($text, 'UTF-8');

        if ($textLength === false) {
            #require_once 'Zend/Text/Figlet/Exception.php';
            throw new Zend_Text_Figlet_Exception('$text is not encoded with ' . $encoding);
        }

        for ($charNum = 0; $charNum < $textLength; $charNum++) {
            // Handle paragraphs
            $char = iconv_substr($text, $charNum, 1, 'UTF-8');

            if ($char === "\n" && $this->_handleParagraphs && !$lastCharWasEol) {
                $nextChar = iconv_substr($text, ($charNum + 1), 1, 'UTF-8');
                if (!$nextChar) {
                    $nextChar = null;
                }

                $char = (ctype_space($nextChar)) ? "\n" : ' ';
            }

            $lastCharWasEol = (ctype_space($char) && $char !== "\t" && $char !== ' ');

            if (ctype_space($char)) {
                $char = ($char === "\t" || $char === ' ') ? ' ': "\n";
            }

            // Skip unprintable characters
            $ordChar = $this->_uniOrd($char);
            if (($ordChar > 0 && $ordChar < 32 && $char !== "\n") || $ordChar === 127) {
                continue;
            }

            // Build the character
            // Note: The following code is complex and thoroughly tested.
            // Be careful when modifying!
            do {
                $charNotAdded = false;

                if ($wordBreakMode === -1) {
                    if ($char === ' ') {
                        break;
                    } else if ($char === "\n") {
                        $wordBreakMode = 0;
                        break;
                    }

                    $wordBreakMode = 0;
                }

                if ($char === "\n") {
                    $this->_appendLine();
                    $wordBreakMode = false;
                } else if ($this->_addChar($char)) {
                    if ($char !== ' ') {
                        $wordBreakMode = ($wordBreakMode >= 2) ? 3: 1;
                    } else {
                        $wordBreakMode = ($wordBreakMode > 0) ? 2: 0;
                    }
                } else if ($this->_outlineLength === 0) {
                    for ($i = 0; $i < $this->_charHeight; $i++) {
                        if ($this->_rightToLeft === 1 && $this->_outputWidth > 1) {
                            $offset = (strlen($this->_currentChar[$i]) - $this->_outlineLengthLimit);
                            $this->_putString(substr($this->_currentChar[$i], $offset));
                        } else {
                            $this->_putString($this->_currentChar[$i]);
                        }
                    }

                    $wordBreakMode = -1;
                } else if ($char === ' ') {
                    if ($wordBreakMode === 2) {
                        $this->_splitLine();
                    } else {
                        $this->_appendLine();
                    }

                    $wordBreakMode = -1;
                } else {
                    if ($wordBreakMode >= 2) {
                        $this->_splitLine();
                    } else {
                        $this->_appendLine();
                    }

                    $wordBreakMode = ($wordBreakMode === 3) ? 1 : 0;
                    $charNotAdded  = true;
                }
            } while ($charNotAdded);
        }

        if ($this->_outlineLength !== 0) {
            $this->_appendLine();
        }

        return $this->_output;
    }

    /**
     * Puts the given string, substituting blanks for hardblanks. If outputWidth
     * is 1, puts the entire string; otherwise puts at most outputWidth - 1
     * characters. Puts a newline at the end of the string. The string is left-
     * justified, centered or right-justified (taking outputWidth as the screen
     * width) if justification is 0, 1 or 2 respectively.
     *
     * @param  string $string The string to add to the output
     * @return void
     */
    protected function _putString($string)
    {
        $length = strlen($string);

        if ($this->_outputWidth > 1) {
            if ($length > ($this->_outputWidth - 1)) {
                $length = ($this->_outputWidth - 1);
            }

            if ($this->_justification > 0) {
                for ($i = 1;
                     ((3 - $this->_justification) * $i + $length + $this->_justification - 2) < $this->_outputWidth;
                     $i++) {
                    $this->_output .= ' ';
                }
            }
        }

        $this->_output .= str_replace($this->_hardBlank, ' ', $string) . "\n";
    }

    /**
     * Appends the current line to the output
     *
     * @return void
     */
    protected function _appendLine()
    {
        for ($i = 0; $i < $this->_charHeight; $i++) {
            $this->_putString($this->_outputLine[$i]);
        }

        $this->_clearLine();
    }

    /**
     * Splits inCharLine at the last word break (bunch of consecutive blanks).
     * Makes a new line out of the first part and appends it using appendLine().
     * Makes a new line out of the second part and returns.
     *
     * @return void
     */
    protected function _splitLine()
    {
        $gotSpace = false;
        for ($i = ($this->_inCharLineLength - 1); $i >= 0; $i--) {
            if (!$gotSpace && $this->_inCharLine[$i] === ' ') {
                $gotSpace  = true;
                $lastSpace = $i;
            }

            if ($gotSpace && $this->_inCharLine[$i] !== ' ') {
                break;
            }
        }

        $firstLength = ($i + 1);
        $lastLength  = ($this->_inCharLineLength - $lastSpace - 1);

        $firstPart = '';
        for ($i = 0; $i < $firstLength; $i++) {
            $firstPart[$i] = $this->_inCharLine[$i];
        }

        $lastPart = '';
        for ($i = 0; $i < $lastLength; $i++) {
            $lastPart[$i] = $this->_inCharLine[($lastSpace + 1 + $i)];
        }

        $this->_clearLine();

        for ($i = 0; $i < $firstLength; $i++) {
            $this->_addChar($firstPart[$i]);
        }

        $this->_appendLine();

        for ($i = 0; $i < $lastLength; $i++) {
            $this->_addChar($lastPart[$i]);
        }
    }

    /**
     * Clears the current line
     *
     * @return void
     */
    protected function _clearLine()
    {
        for ($i = 0; $i < $this->_charHeight; $i++) {
            $this->_outputLine[$i] = '';
        }

        $this->_outlineLength    = 0;
        $this->_inCharLineLength = 0;
    }

    /**
     * Attempts to add the given character onto the end of the current line.
     * Returns true if this can be done, false otherwise.
     *
     * @param  string $char Character which to add to the output
     * @return boolean
     */
    protected function _addChar($char)
    {
        $this->_getLetter($char);

        if ($this->_currentChar === null) {
            return true;
        }

        $smushAmount = $this->_smushAmount();

        if (($this->_outlineLength + $this->_currentCharWidth - $smushAmount) > $this->_outlineLengthLimit
            || ($this->_inCharLineLength + 1) > $this->_inCharLineLengthLimit) {
            return false;
        }

        $tempLine = '';
        for ($row = 0; $row < $this->_charHeight; $row++) {
            if ($this->_rightToLeft === 1) {
                $tempLine = $this->_currentChar[$row];

                for ($k = 0; $k < $smushAmount; $k++) {
                    $position            = ($this->_currentCharWidth - $smushAmount + $k);
                    $tempLine[$position] = $this->_smushem($tempLine[$position], $this->_outputLine[$row][$k]);
                }

                $this->_outputLine[$row] = $tempLine . substr($this->_outputLine[$row], $smushAmount);
            } else {
                for ($k = 0; $k < $smushAmount; $k++) {
                    if (($this->_outlineLength - $smushAmount + $k) < 0) {
                        continue;
                    }

                    $position = ($this->_outlineLength - $smushAmount + $k);
                    if (isset($this->_outputLine[$row][$position])) {
                        $leftChar = $this->_outputLine[$row][$position];
                    } else {
                        $leftChar = null;
                    }

                    $this->_outputLine[$row][$position] = $this->_smushem($leftChar, $this->_currentChar[$row][$k]);
                }

                $this->_outputLine[$row] .= substr($this->_currentChar[$row], $smushAmount);
            }
        }

        $this->_outlineLength                          = strlen($this->_outputLine[0]);
        $this->_inCharLine[$this->_inCharLineLength++] = $char;

        return true;
    }

    /**
     * Gets the requested character and sets current and previous char width.
     *
     * @param  string $char The character from which to get the letter of
     * @return void
     */
    protected function _getLetter($char)
    {
        if (array_key_exists($this->_uniOrd($char), $this->_charList)) {
            $this->_currentChar       = $this->_charList[$this->_uniOrd($char)];
            $this->_previousCharWidth = $this->_currentCharWidth;
            $this->_currentCharWidth  = strlen($this->_currentChar[0]);
        } else {
            $this->_currentChar = null;
        }
    }

    /**
     * Returns the maximum amount that the current character can be smushed into
     * the current line.
     *
     * @return integer
     */
    protected function _smushAmount()
    {
        if (($this->_smushMode & (self::SM_SMUSH | self::SM_KERN)) === 0) {
            return 0;
        }

        $maxSmush = $this->_currentCharWidth;
        $amount   = $maxSmush;

        for ($row = 0; $row < $this->_charHeight; $row++) {
            if ($this->_rightToLeft === 1) {
                $charbd = strlen($this->_currentChar[$row]);
                while (true) {
                    if (!isset($this->_currentChar[$row][$charbd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->_currentChar[$row][$charbd];
                    }

                    if ($charbd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $charbd--;
                    } else {
                        break;
                    }
                }

                $linebd = 0;
                while (true) {
                    if (!isset($this->_outputLine[$row][$linebd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->_outputLine[$row][$linebd];
                    }

                    if ($rightChar === ' ') {
                        $linebd++;
                    } else {
                        break;
                    }
                }

                $amount = ($linebd + $this->_currentCharWidth - 1 - $charbd);
            } else {
                $linebd = strlen($this->_outputLine[$row]);
                while (true) {
                    if (!isset($this->_outputLine[$row][$linebd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->_outputLine[$row][$linebd];
                    }

                    if ($linebd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $linebd--;
                    } else {
                        break;
                    }
                }

                $charbd = 0;
                while (true) {
                    if (!isset($this->_currentChar[$row][$charbd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->_currentChar[$row][$charbd];
                    }

                    if ($rightChar === ' ') {
                        $charbd++;
                    } else {
                        break;
                    }
                }

                $amount = ($charbd + $this->_outlineLength - 1 - $linebd);
            }

            if (empty($leftChar) || $leftChar === ' ') {
                $amount++;
            } else if (!empty($rightChar)) {
                if ($this->_smushem($leftChar, $rightChar) !== null) {
                    $amount++;
                }
            }

            $maxSmush = min($amount, $maxSmush);
        }

        return $maxSmush;
    }

    /**
     * Given two characters, attempts to smush them into one, according to the
     * current smushmode. Returns smushed character or false if no smushing can
     * be done.
     *
     * Smushmode values are sum of following (all values smush blanks):
     *
     *  1: Smush equal chars (not hardblanks)
     *  2: Smush '_' with any char in hierarchy below
     *  4: hierarchy: "|", "/\", "[]", "{}", "()", "<>"
     *     Each class in hier. can be replaced by later class.
     *  8: [ + ] -> |, { + } -> |, ( + ) -> |
     * 16: / + \ -> X, > + < -> X (only in that order)
     * 32: hardblank + hardblank -> hardblank
     *
     * @param  string $leftChar  Left character to smush
     * @param  string $rightChar Right character to smush
     * @return string
     */
    protected function _smushem($leftChar, $rightChar)
    {
        if ($leftChar === ' ') {
            return $rightChar;
        }

        if ($rightChar === ' ') {
            return $leftChar;
        }

        if ($this->_previousCharWidth < 2 || $this->_currentCharWidth < 2) {
            // Disallows overlapping if the previous character or the current
            // character has a width of one or zero.
            return null;
        }

        if (($this->_smushMode & self::SM_SMUSH) === 0) {
            // Kerning
            return null;
        }

        if (($this->_smushMode & 63) === 0) {
            // This is smushing by universal overlapping
            if ($leftChar === ' ') {
                return $rightChar;
            } else if ($rightChar === ' ') {
                return $leftChar;
            } else if ($leftChar === $this->_hardBlank) {
                return $rightChar;
            } else if ($rightChar === $this->_hardBlank) {
                return $rightChar;
            } else if ($this->_rightToLeft === 1) {
                return $leftChar;
            } else {
                // Occurs in the absence of above exceptions
                return $rightChar;
            }
        }

        if (($this->_smushMode & self::SM_HARDBLANK) > 0) {
            if ($leftChar === $this->_hardBlank && $rightChar === $this->_hardBlank) {
                return $leftChar;
            }
        }

        if ($leftChar === $this->_hardBlank && $rightChar === $this->_hardBlank) {
            return null;
        }

        if (($this->_smushMode & self::SM_EQUAL) > 0) {
            if ($leftChar === $rightChar) {
                return $leftChar;
            }
        }

        if (($this->_smushMode & self::SM_LOWLINE) > 0) {
            if ($leftChar === '_' && strchr('|/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } else if ($rightChar === '_' && strchr('|/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->_smushMode & self::SM_HIERARCHY) > 0) {
            if ($leftChar === '|' && strchr('/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } else if ($rightChar === '|' && strchr('/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } else if (strchr('/\\', $leftChar) && strchr('[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } else if (strchr('/\\', $rightChar) && strchr('[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } else if (strchr('[]', $leftChar) && strchr('{}()<>', $rightChar) !== false) {
                return $rightChar;
            } else if (strchr('[]', $rightChar) && strchr('{}()<>', $leftChar) !== false) {
                return $leftChar;
            } else if (strchr('{}', $leftChar) && strchr('()<>', $rightChar) !== false) {
                return $rightChar;
            } else if (strchr('{}', $rightChar) && strchr('()<>', $leftChar) !== false) {
                return $leftChar;
            } else if (strchr('()', $leftChar) && strchr('<>', $rightChar) !== false) {
                return $rightChar;
            } else if (strchr('()', $rightChar) && strchr('<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->_smushMode & self::SM_PAIR) > 0) {
            if ($leftChar === '[' && $rightChar === ']') {
                return '|';
            } else if ($rightChar === '[' && $leftChar === ']') {
                return '|';
            } else if ($leftChar === '{' && $rightChar === '}') {
                return '|';
            } else if ($rightChar === '{' && $leftChar === '}') {
                return '|';
            } else if ($leftChar === '(' && $rightChar === ')') {
                return '|';
            } else if ($rightChar === '(' && $leftChar === ')') {
                return '|';
            }
        }

        if (($this->_smushMode & self::SM_BIGX) > 0) {
            if ($leftChar === '/' && $rightChar === '\\') {
                return '|';
            } else if ($rightChar === '/' && $leftChar === '\\') {
                return 'Y';
            } else if ($leftChar === '>' && $rightChar === '<') {
                return 'X';
            }
        }

        return null;
    }

    /**
     * Load the specified font
     *
     * @param  string $fontFile Font file to load
     * @throws Zend_Text_Figlet_Exception When font file was not found
     * @throws Zend_Text_Figlet_Exception When GZIP library is required but not found
     * @throws Zend_Text_Figlet_Exception When font file is not readable
     * @return void
     */
    protected function _loadFont($fontFile)
    {
        // Check if the font file exists
        if (!file_exists($fontFile)) {
            #require_once 'Zend/Text/Figlet/Exception.php';
            throw new Zend_Text_Figlet_Exception($fontFile . ': Font file not found');
        }

        // Check if gzip support is required
        if (substr($fontFile, -3) === '.gz') {
            if (!function_exists('gzcompress')) {
                #require_once 'Zend/Text/Figlet/Exception.php';
                throw new Zend_Text_Figlet_Exception('GZIP library is required for '
                                                     . 'gzip compressed font files');
            }

            $fontFile   = 'compress.zlib://' . $fontFile;
            $compressed = true;
        } else {
            $compressed = false;
        }

        // Try to open the file
        $fp = fopen($fontFile, 'rb');
        if ($fp === false) {
            #require_once 'Zend/Text/Figlet/Exception.php';
            throw new Zend_Text_Figlet_Exception($fontFile . ': Could not open file');
        }

        // If the file is not compressed, lock the stream
        if (!$compressed) {
            flock($fp, LOCK_SH);
        }

        // Get magic
        $magic = $this->_readMagic($fp);

        // Get the header
        $numsRead = sscanf(fgets($fp, 1000),
                           '%*c%c %d %*d %d %d %d %d %d',
                           $this->_hardBlank,
                           $this->_charHeight,
                           $this->_maxLength,
                           $smush,
                           $cmtLines,
                           $rightToLeft,
                           $this->_fontSmush);

        if ($magic !== self::FONTFILE_MAGIC_NUMBER || $numsRead < 5) {
            #require_once 'Zend/Text/Figlet/Exception.php';
            throw new Zend_Text_Figlet_Exception($fontFile . ': Not a FIGlet 2 font file');
        }

        // Set default right to left
        if ($numsRead < 6) {
            $rightToLeft = 0;
        }

        // If no smush2, decode smush into smush2
        if ($numsRead < 7) {
            if ($smush === 2) {
                $this->_fontSmush = self::SM_KERN;
            } else if ($smush < 0) {
                $this->_fontSmush = 0;
            } else {
                $this->_fontSmush = (($smush & 31) | self::SM_SMUSH);
            }
        }

        // Correct char height && maxlength
        $this->_charHeight = max(1, $this->_charHeight);
        $this->_maxLength  = max(1, $this->_maxLength);

        // Give ourselves some extra room
        $this->_maxLength += 100;

        // See if we have to override smush settings
        $this->_setUsedSmush();

        // Get left to right value
        if ($this->_rightToLeft === null) {
            $this->_rightToLeft = $rightToLeft;
        }

        // Get justification value
        if ($this->_justification === null) {
            $this->_justification = (2 * $this->_rightToLeft);
        }

        // Skip all comment lines
        for ($line = 1; $line <= $cmtLines; $line++) {
            $this->_skipToEol($fp);
        }

        // Fetch all ASCII characters
        for ($asciiCode = 32; $asciiCode < 127; $asciiCode++) {
            $this->_charList[$asciiCode] = $this->_loadChar($fp);
        }

        // Fetch all german characters
        foreach ($this->_germanChars as $uniCode) {
            $char = $this->_loadChar($fp);

            if ($char === false) {
                fclose($fp);
                return;
            }

            if (trim(implode('', $char)) !== '') {
                $this->_charList[$uniCode] = $char;
            }
        }

        // At the end fetch all extended characters
        while (!feof($fp)) {
            // Get the Unicode
            list($uniCode) = explode(' ', fgets($fp, 2048));

            if (empty($uniCode)) {
                continue;
            }

            // Convert it if required
            if (substr($uniCode, 0, 2) === '0x') {
                $uniCode = hexdec(substr($uniCode, 2));
            } else if (substr($uniCode, 0, 1) === '0' and
                       $uniCode !== '0' or
                       substr($uniCode, 0, 2) === '-0') {
                $uniCode = octdec($uniCode);
            } else {
                $uniCode = (int) $uniCode;
            }

            // Now fetch the character
            $char = $this->_loadChar($fp);

            if ($char === false) {
                fclose($fp);
                return;
            }

            $this->_charList[$uniCode] = $char;
        }

        fclose($fp);

        $this->_fontLoaded = true;
    }

    /**
     * Set the used smush mode, according to smush override, user smsush and
     * font smush.
     *
     * @return void
     */
    protected function _setUsedSmush()
    {
        if ($this->_smushOverride === self::SMO_NO) {
            $this->_smushMode = $this->_fontSmush;
        } else if ($this->_smushOverride === self::SMO_YES) {
            $this->_smushMode = $this->_userSmush;
        } else if ($this->_smushOverride === self::SMO_FORCE) {
            $this->_smushMode = ($this->_fontSmush | $this->_userSmush);
        }
    }

    /**
     * Reads a four-character magic string from a stream
     *
     * @param  resource $fp File pointer to the font file
     * @return string
     */
    protected function _readMagic($fp)
    {
        $magic = '';

        for ($i = 0; $i < 4; $i++) {
            $magic .= fgetc($fp);
        }

        return $magic;
    }

    /**
     * Skip a stream to the end of line
     *
     * @param  resource $fp File pointer to the font file
     * @return void
     */
    protected function _skipToEol($fp)
    {
        $dummy = fgetc($fp);
        while ($dummy !== false && !feof($fp)) {
            if ($dummy === "\n") {
                return;
            }

            if ($dummy === "\r") {
                $dummy = fgetc($fp);

                if (!feof($fp) && $dummy !== "\n") {
                    fseek($fp, -1, SEEK_SET);
                }

                return;
            }

            $dummy = fgetc($fp);
        }
    }

    /**
     * Load a single character from the font file
     *
     * @param  resource $fp File pointer to the font file
     * @return array
     */
    protected function _loadChar($fp)
    {
        $char = array();

        for ($i = 0; $i < $this->_charHeight; $i++) {
            if (feof($fp)) {
                return false;
            }

            $line = rtrim(fgets($fp, 2048), "\r\n");

            if (preg_match('#(.)\\1?$#', $line, $result) === 1) {
                $line = str_replace($result[1], '', $line);
            }

            $char[] = $line;
        }

        return $char;
    }

    /**
     * Unicode compatible ord() method
     *
     * @param  string $c The char to get the value from
     * @return integer
     */
    protected function _uniOrd($c)
    {
        $h = ord($c[0]);

        if ($h <= 0x7F) {
            $ord = $h;
        } else if ($h < 0xC2) {
            $ord = 0;
        } else if ($h <= 0xDF) {
            $ord = (($h & 0x1F) << 6 | (ord($c[1]) & 0x3F));
        } else if ($h <= 0xEF) {
            $ord = (($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F));
        } else if ($h <= 0xF4) {
            $ord = (($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 |
                   (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F));
        } else {
            $ord = 0;
        }

        return $ord;
    }
}
