<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Text\Figlet;

use Traversable;
use Zend\Stdlib\ArrayUtils;
use Zend\Stdlib\StringUtils;

/**
 * Zend\Text\Figlet is a PHP implementation of FIGlet
 */
class Figlet
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
    protected $charList = array();

    /**
     * Indicates if a font was loaded yet
     *
     * @var bool
     */
    protected $fontLoaded = false;

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
    protected $germanChars = array(196, 214, 220, 228, 246, 252, 223);

    /**
     * Output width, defaults to 80.
     *
     * @var int
     */
    protected $outputWidth = 80;

    /**
     * Hard blank character
     *
     * @var string
     */
    protected $hardBlank;

    /**
     * Height of the characters
     *
     * @var int
     */
    protected $charHeight;

    /**
     * Max length of any character
     *
     * @var int
     */
    protected $maxLength;

    /**
     * Smush mode
     *
     * @var int
     */
    protected $smushMode = 0;

    /**
     * Smush defined by the font
     *
     * @var int
     */
    protected $fontSmush = 0;

    /**
     * Smush defined by the user
     *
     * @var int
     */
    protected $userSmush = 0;

    /**
     * Whether to handle paragraphs || not
     *
     * @var bool
     */
    protected $handleParagraphs = false;

    /**
     * Justification for the text, according to $outputWidth
     *
     * For using font default, this parameter should be null, else one of
     * the values of Zend\Text\Figlet::JUSTIFICATION_*
     *
     * @var int
     */
    protected $justification = null;

    /**
     * Direction of text-writing, namely right to left
     *
     * For using font default, this parameter should be null, else one of
     * the values of Zend\Text\Figlet::DIRECTION_*
     *
     * @var int
     */
    protected $rightToLeft = null;

    /**
     * Override font file smush layout
     *
     * @var int
     */
    protected $smushOverride = 0;

    /**
     * Options of the current font
     *
     * @var array
     */
    protected $fontOptions = array();

    /**
     * Previous character width
     *
     * @var int
     */
    protected $previousCharWidth = 0;

    /**
     * Current character width
     *
     * @var int
     */
    protected $currentCharWidth = 0;

    /**
     * Current outline length
     *
     * @var int
     */
    protected $outlineLength = 0;

    /**
     * Maximum outline length
     *
     * @var int
     */
    protected $outlineLengthLimit = 0;

    /**
     * In character line
     *
     * @var string
     */
    protected $inCharLine;

    /**
     * In character line length
     *
     * @var int
     */
    protected $inCharLineLength = 0;

    /**
     * Maximum in character line length
     *
     * @var int
     */
    protected $inCharLineLengthLimit = 0;

    /**
     * Current char
     *
     * @var array
     */
    protected $currentChar = null;

    /**
     * Current output line
     *
     * @var array
     */
    protected $outputLine;

    /**
     * Current output
     *
     * @var string
     */
    protected $output;

    /**
     * Option keys to skip when calling setOptions()
     *
     * @var array
     */
    protected $skipOptions = array(
        'options',
        'config',
    );

    /**
     * Instantiate the FIGlet with a specific font. If no font is given, the
     * standard font is used. You can also supply multiple options via
     * the $options variable, which can either be an array or an instance of
     * Zend\Config\Config.
     *
     * @param array|Traversable $options Options for the output
     */
    public function __construct($options = null)
    {
        // Set options
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }

        // If no font was defined, load default font
        if (!$this->fontLoaded) {
            $this->_loadFont(__DIR__ . '/zend-framework.flf');
        }
    }

    /**
     * Set options from array
     *
     * @param  array $options Configuration for Figlet
     * @return Figlet
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            if (in_array(strtolower($key), $this->skipOptions)) {
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
     * Set a font to use
     *
     * @param  string $font Path to the font
     * @return Figlet
     */
    public function setFont($font)
    {
        $this->_loadFont($font);
        return $this;
    }

    /**
     * Set handling of paragraphs
     *
     * @param  bool $handleParagraphs Whether to handle paragraphs or not
     * @return Figlet
     */
    public function setHandleParagraphs($handleParagraphs)
    {
        $this->handleParagraphs = (bool) $handleParagraphs;
        return $this;
    }

    /**
     * Set the justification. 0 stands for left aligned, 1 for centered and 2
     * for right aligned.
     *
     * @param  int $justification Justification of the output text
     * @return Figlet
     */
    public function setJustification($justification)
    {
        $this->justification = min(3, max(0, (int) $justification));
        return $this;
    }

    /**
     * Set the output width
     *
     * @param  int $outputWidth Output with which should be used for word
     *                              wrapping and justification
     * @return Figlet
     */
    public function setOutputWidth($outputWidth)
    {
        $this->outputWidth = max(1, (int) $outputWidth);
        return $this;
    }

    /**
     * Set right to left mode. For writing from left to right, use
     * Zend\Text\Figlet::DIRECTION_LEFT_TO_RIGHT. For writing from right to left,
     * use Zend\Text\Figlet::DIRECTION_RIGHT_TO_LEFT.
     *
     * @param  int $rightToLeft Right-to-left mode
     * @return Figlet
     */
    public function setRightToLeft($rightToLeft)
    {
        $this->rightToLeft = min(1, max(0, (int) $rightToLeft));
        return $this;
    }

    /**
     * Set the smush mode.
     *
     * Use one of the constants of Zend\Text\Figlet::SM_*, you may combine them.
     *
     * @param  int $smushMode Smush mode to use for generating text
     * @return Figlet
     */
    public function setSmushMode($smushMode)
    {
        $smushMode = (int) $smushMode;

        if ($smushMode < -1) {
            $this->smushOverride = self::SMO_NO;
        } else {
            if ($smushMode === 0) {
                $this->userSmush = self::SM_KERN;
            } elseif ($smushMode === -1) {
                $this->userSmush = 0;
            } else {
                $this->userSmush = (($smushMode & 63) | self::SM_SMUSH);
            }

            $this->smushOverride = self::SMO_YES;
        }

        $this->_setUsedSmush();

        return $this;
    }

    /**
     * Render a FIGlet text
     *
     * @param  string $text     Text to convert to a figlet text
     * @param  string $encoding Encoding of the input string
     * @throws Exception\InvalidArgumentException When $text is not a string
     * @throws Exception\UnexpectedValueException When $text it not properly encoded
     * @return string
     */
    public function render($text, $encoding = 'UTF-8')
    {
        if (!is_string($text)) {
            throw new Exception\InvalidArgumentException('$text must be a string');
        }

        // Get the string wrapper supporting UTF-8 character encoding and the input encoding
        $strWrapper = StringUtils::getWrapper($encoding, 'UTF-8');

        // Convert $text to UTF-8 and check encoding
        $text = $strWrapper->convert($text);
        if (!StringUtils::isValidUtf8($text)) {
            throw new Exception\UnexpectedValueException('$text is not encoded with ' . $encoding);
        }

        $strWrapper = StringUtils::getWrapper('UTF-8');

        $this->output     = '';
        $this->outputLine = array();

        $this->_clearLine();

        $this->outlineLengthLimit    = ($this->outputWidth - 1);
        $this->inCharLineLengthLimit = ($this->outputWidth * 4 + 100);

        $wordBreakMode  = 0;
        $lastCharWasEol = false;
        $textLength     = $strWrapper->strlen($text);

        for ($charNum = 0; $charNum < $textLength; $charNum++) {
            // Handle paragraphs
            $char = $strWrapper->substr($text, $charNum, 1);

            if ($char === "\n" && $this->handleParagraphs && !$lastCharWasEol) {
                $nextChar = $strWrapper->substr($text, ($charNum + 1), 1);
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
                    } elseif ($char === "\n") {
                        $wordBreakMode = 0;
                        break;
                    }

                    $wordBreakMode = 0;
                }

                if ($char === "\n") {
                    $this->_appendLine();
                    $wordBreakMode = false;
                } elseif ($this->_addChar($char)) {
                    if ($char !== ' ') {
                        $wordBreakMode = ($wordBreakMode >= 2) ? 3: 1;
                    } else {
                        $wordBreakMode = ($wordBreakMode > 0) ? 2: 0;
                    }
                } elseif ($this->outlineLength === 0) {
                    for ($i = 0; $i < $this->charHeight; $i++) {
                        if ($this->rightToLeft === 1 && $this->outputWidth > 1) {
                            $offset = (strlen($this->currentChar[$i]) - $this->outlineLengthLimit);
                            $this->_putString(substr($this->currentChar[$i], $offset));
                        } else {
                            $this->_putString($this->currentChar[$i]);
                        }
                    }

                    $wordBreakMode = -1;
                } elseif ($char === ' ') {
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

        if ($this->outlineLength !== 0) {
            $this->_appendLine();
        }

        return $this->output;
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

        if ($this->outputWidth > 1) {
            if ($length > ($this->outputWidth - 1)) {
                $length = ($this->outputWidth - 1);
            }

            if ($this->justification > 0) {
                for ($i = 1;
                     ((3 - $this->justification) * $i + $length + $this->justification - 2) < $this->outputWidth;
                     $i++) {
                    $this->output .= ' ';
                }
            }
        }

        $this->output .= str_replace($this->hardBlank, ' ', $string) . "\n";
    }

    /**
     * Appends the current line to the output
     *
     * @return void
     */
    protected function _appendLine()
    {
        for ($i = 0; $i < $this->charHeight; $i++) {
            $this->_putString($this->outputLine[$i]);
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
        for ($i = ($this->inCharLineLength - 1); $i >= 0; $i--) {
            if (!$gotSpace && $this->inCharLine[$i] === ' ') {
                $gotSpace  = true;
                $lastSpace = $i;
            }

            if ($gotSpace && $this->inCharLine[$i] !== ' ') {
                break;
            }
        }

        $firstLength = ($i + 1);
        $lastLength  = ($this->inCharLineLength - $lastSpace - 1);

        $firstPart = '';
        for ($i = 0; $i < $firstLength; $i++) {
            $firstPart[$i] = $this->inCharLine[$i];
        }

        $lastPart = '';
        for ($i = 0; $i < $lastLength; $i++) {
            $lastPart[$i] = $this->inCharLine[($lastSpace + 1 + $i)];
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
        for ($i = 0; $i < $this->charHeight; $i++) {
            $this->outputLine[$i] = '';
        }

        $this->outlineLength    = 0;
        $this->inCharLineLength = 0;
    }

    /**
     * Attempts to add the given character onto the end of the current line.
     * Returns true if this can be done, false otherwise.
     *
     * @param  string $char Character which to add to the output
     * @return bool
     */
    protected function _addChar($char)
    {
        $this->_getLetter($char);

        if ($this->currentChar === null) {
            return true;
        }

        $smushAmount = $this->_smushAmount();

        if (($this->outlineLength + $this->currentCharWidth - $smushAmount) > $this->outlineLengthLimit
            || ($this->inCharLineLength + 1) > $this->inCharLineLengthLimit) {
            return false;
        }

        for ($row = 0; $row < $this->charHeight; $row++) {
            if ($this->rightToLeft === 1) {
                $tempLine = $this->currentChar[$row];

                for ($k = 0; $k < $smushAmount; $k++) {
                    $position            = ($this->currentCharWidth - $smushAmount + $k);
                    $tempLine[$position] = $this->_smushem($tempLine[$position], $this->outputLine[$row][$k]);
                }

                $this->outputLine[$row] = $tempLine . substr($this->outputLine[$row], $smushAmount);
            } else {
                for ($k = 0; $k < $smushAmount; $k++) {
                    if (($this->outlineLength - $smushAmount + $k) < 0) {
                        continue;
                    }

                    $position = ($this->outlineLength - $smushAmount + $k);
                    if (isset($this->outputLine[$row][$position])) {
                        $leftChar = $this->outputLine[$row][$position];
                    } else {
                        $leftChar = null;
                    }

                    $this->outputLine[$row][$position] = $this->_smushem($leftChar, $this->currentChar[$row][$k]);
                }

                $this->outputLine[$row] .= substr($this->currentChar[$row], $smushAmount);
            }
        }

        $this->outlineLength                         = strlen($this->outputLine[0]);
        $this->inCharLine[$this->inCharLineLength++] = $char;

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
        if (array_key_exists($this->_uniOrd($char), $this->charList)) {
            $this->currentChar       = $this->charList[$this->_uniOrd($char)];
            $this->previousCharWidth = $this->currentCharWidth;
            $this->currentCharWidth  = strlen($this->currentChar[0]);
        } else {
            $this->currentChar = null;
        }
    }

    /**
     * Returns the maximum amount that the current character can be smushed into
     * the current line.
     *
     * @return int
     */
    protected function _smushAmount()
    {
        if (($this->smushMode & (self::SM_SMUSH | self::SM_KERN)) === 0) {
            return 0;
        }

        $maxSmush = $this->currentCharWidth;

        for ($row = 0; $row < $this->charHeight; $row++) {
            if ($this->rightToLeft === 1) {
                $charbd = strlen($this->currentChar[$row]);
                while (true) {
                    if (!isset($this->currentChar[$row][$charbd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->currentChar[$row][$charbd];
                    }

                    if ($charbd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $charbd--;
                    } else {
                        break;
                    }
                }

                $linebd = 0;
                while (true) {
                    if (!isset($this->outputLine[$row][$linebd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->outputLine[$row][$linebd];
                    }

                    if ($rightChar === ' ') {
                        $linebd++;
                    } else {
                        break;
                    }
                }

                $amount = ($linebd + $this->currentCharWidth - 1 - $charbd);
            } else {
                $linebd = strlen($this->outputLine[$row]);
                while (true) {
                    if (!isset($this->outputLine[$row][$linebd])) {
                        $leftChar = null;
                    } else {
                        $leftChar = $this->outputLine[$row][$linebd];
                    }

                    if ($linebd > 0 && ($leftChar === null || $leftChar == ' ')) {
                        $linebd--;
                    } else {
                        break;
                    }
                }

                $charbd = 0;
                while (true) {
                    if (!isset($this->currentChar[$row][$charbd])) {
                        $rightChar = null;
                    } else {
                        $rightChar = $this->currentChar[$row][$charbd];
                    }

                    if ($rightChar === ' ') {
                        $charbd++;
                    } else {
                        break;
                    }
                }

                $amount = ($charbd + $this->outlineLength - 1 - $linebd);
            }

            if (empty($leftChar) || $leftChar === ' ') {
                $amount++;
            } elseif (!empty($rightChar)) {
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

        if ($this->previousCharWidth < 2 || $this->currentCharWidth < 2) {
            // Disallows overlapping if the previous character or the current
            // character has a width of one or zero.
            return;
        }

        if (($this->smushMode & self::SM_SMUSH) === 0) {
            // Kerning
            return;
        }

        if (($this->smushMode & 63) === 0) {
            // This is smushing by universal overlapping
            if ($leftChar === ' ') {
                return $rightChar;
            } elseif ($rightChar === ' ') {
                return $leftChar;
            } elseif ($leftChar === $this->hardBlank) {
                return $rightChar;
            } elseif ($rightChar === $this->hardBlank) {
                return $rightChar;
            } elseif ($this->rightToLeft === 1) {
                return $leftChar;
            } else {
                // Occurs in the absence of above exceptions
                return $rightChar;
            }
        }

        if (($this->smushMode & self::SM_HARDBLANK) > 0) {
            if ($leftChar === $this->hardBlank && $rightChar === $this->hardBlank) {
                return $leftChar;
            }
        }

        if ($leftChar === $this->hardBlank && $rightChar === $this->hardBlank) {
            return;
        }

        if (($this->smushMode & self::SM_EQUAL) > 0) {
            if ($leftChar === $rightChar) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_LOWLINE) > 0) {
            if ($leftChar === '_' && strchr('|/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif ($rightChar === '_' && strchr('|/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_HIERARCHY) > 0) {
            if ($leftChar === '|' && strchr('/\\[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif ($rightChar === '|' && strchr('/\\[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('/\\', $leftChar) && strchr('[]{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('/\\', $rightChar) && strchr('[]{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('[]', $leftChar) && strchr('{}()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('[]', $rightChar) && strchr('{}()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('{}', $leftChar) && strchr('()<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('{}', $rightChar) && strchr('()<>', $leftChar) !== false) {
                return $leftChar;
            } elseif (strchr('()', $leftChar) && strchr('<>', $rightChar) !== false) {
                return $rightChar;
            } elseif (strchr('()', $rightChar) && strchr('<>', $leftChar) !== false) {
                return $leftChar;
            }
        }

        if (($this->smushMode & self::SM_PAIR) > 0) {
            if ($leftChar === '[' && $rightChar === ']') {
                return '|';
            } elseif ($rightChar === '[' && $leftChar === ']') {
                return '|';
            } elseif ($leftChar === '{' && $rightChar === '}') {
                return '|';
            } elseif ($rightChar === '{' && $leftChar === '}') {
                return '|';
            } elseif ($leftChar === '(' && $rightChar === ')') {
                return '|';
            } elseif ($rightChar === '(' && $leftChar === ')') {
                return '|';
            }
        }

        if (($this->smushMode & self::SM_BIGX) > 0) {
            if ($leftChar === '/' && $rightChar === '\\') {
                return '|';
            } elseif ($rightChar === '/' && $leftChar === '\\') {
                return 'Y';
            } elseif ($leftChar === '>' && $rightChar === '<') {
                return 'X';
            }
        }

        return;
    }

    /**
     * Load the specified font
     *
     * @param  string $fontFile Font file to load
     * @throws Exception\RuntimeException When font file was not found
     * @throws Exception\RuntimeException When GZIP library is required but not found
     * @throws Exception\RuntimeException When font file is not readable
     * @throws Exception\UnexpectedValueException When font file is not a FIGlet 2 font file
     * @return void
     */
    protected function _loadFont($fontFile)
    {
        // Check if the font file exists
        if (!file_exists($fontFile)) {
            throw new Exception\RuntimeException($fontFile . ': Font file not found');
        }

        // Check if gzip support is required
        if (substr($fontFile, -3) === '.gz') {
            if (!function_exists('gzcompress')) {
                throw new Exception\RuntimeException('GZIP library is required for gzip compressed font files');
            }

            $fontFile   = 'compress.zlib://' . $fontFile;
            $compressed = true;
        } else {
            $compressed = false;
        }

        // Try to open the file
        $fp = fopen($fontFile, 'rb');
        if ($fp === false) {
            throw new Exception\RuntimeException($fontFile . ': Could not open file');
        }

        // If the file is not compressed, lock the stream
        if (!$compressed) {
            flock($fp, LOCK_SH);
        }

        // Get magic
        $magic = $this->_readMagic($fp);

        // Get the header
        $line = fgets($fp, 1000) ?: '';
        $numsRead = sscanf(
            $line,
            '%*c%c %d %*d %d %d %d %d %d',
            $this->hardBlank,
            $this->charHeight,
            $this->maxLength,
            $smush,
            $cmtLines,
            $rightToLeft,
            $this->fontSmush
        );

        if ($magic !== self::FONTFILE_MAGIC_NUMBER || $numsRead < 5) {
            throw new Exception\UnexpectedValueException($fontFile . ': Not a FIGlet 2 font file');
        }

        // Set default right to left
        if ($numsRead < 6) {
            $rightToLeft = 0;
        }

        // If no smush2, decode smush into smush2
        if ($numsRead < 7) {
            if ($smush === 2) {
                $this->fontSmush = self::SM_KERN;
            } elseif ($smush < 0) {
                $this->fontSmush = 0;
            } else {
                $this->fontSmush = (($smush & 31) | self::SM_SMUSH);
            }
        }

        // Correct char height && maxlength
        $this->charHeight = max(1, $this->charHeight);
        $this->maxLength  = max(1, $this->maxLength);

        // Give ourselves some extra room
        $this->maxLength += 100;

        // See if we have to override smush settings
        $this->_setUsedSmush();

        // Get left to right value
        if ($this->rightToLeft === null) {
            $this->rightToLeft = $rightToLeft;
        }

        // Get justification value
        if ($this->justification === null) {
            $this->justification = (2 * $this->rightToLeft);
        }

        // Skip all comment lines
        for ($line = 1; $line <= $cmtLines; $line++) {
            $this->_skipToEol($fp);
        }

        // Fetch all ASCII characters
        for ($asciiCode = 32; $asciiCode < 127; $asciiCode++) {
            $this->charList[$asciiCode] = $this->_loadChar($fp);
        }

        // Fetch all german characters
        foreach ($this->germanChars as $uniCode) {
            $char = $this->_loadChar($fp);

            if ($char === false) {
                fclose($fp);
                return;
            }

            if (trim(implode('', $char)) !== '') {
                $this->charList[$uniCode] = $char;
            }
        }

        // At the end fetch all extended characters
        while (!feof($fp)) {
            // Get the Unicode
            $uniCode = fgets($fp, 2048);

            if (false === $uniCode) {
                continue;
            }

            list($uniCode) = explode(' ', $uniCode);

            if (empty($uniCode)) {
                continue;
            }

            // Convert it if required
            if (substr($uniCode, 0, 2) === '0x') {
                $uniCode = hexdec(substr($uniCode, 2));
            } elseif (substr($uniCode, 0, 1) === '0' and
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

            $this->charList[$uniCode] = $char;
        }

        fclose($fp);

        $this->fontLoaded = true;
    }

    /**
     * Set the used smush mode, according to smush override, user smush and
     * font smush.
     *
     * @return void
     */
    protected function _setUsedSmush()
    {
        if ($this->smushOverride === self::SMO_NO) {
            $this->smushMode = $this->fontSmush;
        } elseif ($this->smushOverride === self::SMO_YES) {
            $this->smushMode = $this->userSmush;
        } elseif ($this->smushOverride === self::SMO_FORCE) {
            $this->smushMode = ($this->fontSmush | $this->userSmush);
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

        for ($i = 0; $i < $this->charHeight; $i++) {
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
     * @return int
     */
    protected function _uniOrd($c)
    {
        $h = ord($c[0]);

        if ($h <= 0x7F) {
            $ord = $h;
        } elseif ($h < 0xC2) {
            $ord = 0;
        } elseif ($h <= 0xDF) {
            $ord = (($h & 0x1F) << 6 | (ord($c[1]) & 0x3F));
        } elseif ($h <= 0xEF) {
            $ord = (($h & 0x0F) << 12 | (ord($c[1]) & 0x3F) << 6 | (ord($c[2]) & 0x3F));
        } elseif ($h <= 0xF4) {
            $ord = (($h & 0x0F) << 18 | (ord($c[1]) & 0x3F) << 12 |
                   (ord($c[2]) & 0x3F) << 6 | (ord($c[3]) & 0x3F));
        } else {
            $ord = 0;
        }

        return $ord;
    }
}
