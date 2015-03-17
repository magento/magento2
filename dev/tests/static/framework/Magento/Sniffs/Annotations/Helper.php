<?php
namespace Magento\Sniffs\Annotations;

use PHP_CodeSniffer;
use PHP_CodeSniffer_File;

/**
 * Base of the annotations sniffs
 *
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2012 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 *
 * @SuppressWarnings(PHPMD)
 */
class Helper
{
    const ERROR_PARSING = 'ErrorParsing';

    const AMBIGUOUS_TYPE = 'AmbiguousType';

    const MISSING = 'Missing';

    const WRONG_STYLE = 'WrongStyle';

    const WRONG_END = 'WrongEnd';

    const FAILED_PARSE = 'FailedParse';

    const CONTENT_AFTER_OPEN = 'ContentAfterOpen';

    const MISSING_SHORT = 'MissingShort';

    const EMPTY_DOC = 'Empty';

    const SPACING_BETWEEN = 'SpacingBetween';

    const SPACING_BEFORE_SHORT = 'SpacingBeforeShort';

    const SPACING_BEFORE_TAGS = 'SpacingBeforeTags';

    const SHORT_SINGLE_LINE = 'ShortSingleLine';

    const SHORT_NOT_CAPITAL = 'ShortNotCapital';

    const SHORT_FULL_STOP = 'ShortFullStop';

    const SPACING_AFTER = 'SpacingAfter';

    const SEE_ORDER = 'SeeOrder';

    const EMPTY_SEE = 'EmptySee';

    const SEE_INDENT = 'SeeIndent';

    const DUPLICATE_RETURN = 'DuplicateReturn';

    const MISSING_PARAM_TAG = 'MissingParamTag';

    const SPACING_AFTER_LONG_NAME = 'SpacingAfterLongName';

    const SPACING_AFTER_LONG_TYPE = 'SpacingAfterLongType';

    const MISSING_PARAM_TYPE = 'MissingParamType';

    const MISSING_PARAM_NAME = 'MissingParamName';

    const EXTRA_PARAM_COMMENT = 'ExtraParamComment';

    const PARAM_NAME_NO_MATCH = 'ParamNameNoMatch';

    const PARAM_NAME_NO_CASE_MATCH = 'ParamNameNoCaseMatch';

    const INVALID_TYPE_HINT = 'InvalidTypeHint';

    const INCORRECT_TYPE_HINT = 'IncorrectTypeHint';

    const TYPE_HINT_MISSING = 'TypeHintMissing';

    const INCORRECT_PARAM_VAR_NAME = 'IncorrectParamVarName';

    const RETURN_ORDER = 'ReturnOrder';

    const MISSING_RETURN_TYPE = 'MissingReturnType';

    const INVALID_RETURN = 'InvalidReturn';

    const INVALID_RETURN_VOID = 'InvalidReturnVoid';

    const INVALID_NO_RETURN = 'InvalidNoReturn';

    const INVALID_RETURN_NOT_VOID = 'InvalidReturnNotVoid';

    const INCORRECT_INHERIT_DOC = 'IncorrectInheritDoc';

    const RETURN_INDENT = 'ReturnIndent';

    const MISSING_RETURN = 'MissingReturn';

    const RETURN_NOT_REQUIRED = 'ReturnNotRequired';

    const INVALID_THROWS = 'InvalidThrows';

    const THROWS_NOT_CAPITAL = 'ThrowsNotCapital';

    const THROWS_ORDER = 'ThrowsOrder';

    const EMPTY_THROWS = 'EmptyThrows';

    const THROWS_NO_FULL_STOP = 'ThrowsNoFullStop';

    const SPACING_AFTER_PARAMS = 'SpacingAfterParams';

    const SPACING_BEFORE_PARAMS = 'SpacingBeforeParams';

    const SPACING_BEFORE_PARAM_TYPE = 'SpacingBeforeParamType';

    const LONG_NOT_CAPITAL = 'LongNotCapital';

    const TAG_NOT_ALLOWED = 'TagNotAllowed';

    const DUPLICATE_VAR = 'DuplicateVar';

    const VAR_ORDER = 'VarOrder';

    const MISSING_VAR_TYPE = 'MissingVarType';

    const INCORRECT_VAR_TYPE = 'IncorrectVarType';

    const VAR_INDENT = 'VarIndent';

    const MISSING_VAR = 'MissingVar';

    const MISSING_PARAM_COMMENT = 'MissingParamComment';

    const PARAM_COMMENT_NOT_CAPITAL = 'ParamCommentNotCapital';

    const PARAM_COMMENT_FULL_STOP = 'ParamCommentFullStop';

    // tells phpcs to use the default level
    const ERROR = 0;

    // default level of warnings is 5
    const WARNING = 6;

    const INFO = 2;

    // Lowest possible level.
    const OFF = 1;

    const LEVEL = 'level';

    const MESSAGE = 'message';

    /**
     * Map of Error Type to Error Severity
     *
     * @var array
     */
    protected static $reportingLevel = [
        self::ERROR_PARSING => [self::LEVEL => self::ERROR, self::MESSAGE => '%s'],
        self::FAILED_PARSE => [self::LEVEL => self::ERROR, self::MESSAGE => '%s'],
        self::AMBIGUOUS_TYPE => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Ambiguous type "%s" for %s is NOT recommended',
        ],
        self::MISSING => [self::LEVEL => self::ERROR, self::MESSAGE => 'Missing %s doc comment'],
        self::WRONG_STYLE => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'You must use "/**" style comments for a %s comment',
        ],
        self::WRONG_END => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'You must use "*/" to end a function comment; found "%s"',
        ],
        self::EMPTY_DOC => [self::LEVEL => self::WARNING, self::MESSAGE => '%s doc comment is empty'],
        self::CONTENT_AFTER_OPEN => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The open comment tag must be the only content on the line',
        ],
        self::MISSING_SHORT => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Missing short description in %s doc comment',
        ],
        self::SPACING_BETWEEN => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'There must be exactly one blank line between descriptions in %s comment',
        ],
        self::SPACING_BEFORE_SHORT => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Extra newline(s) found before %s comment short description',
        ],
        self::SPACING_BEFORE_TAGS => [
            self::LEVEL => self::INFO,
            self::MESSAGE => 'There must be exactly one blank line before the tags in %s comment',
        ],
        self::SHORT_SINGLE_LINE => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '%s comment short description must be on a single line',
        ],
        self::SHORT_NOT_CAPITAL => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => '%s comment short description must start with a capital letter',
        ],
        self::SHORT_FULL_STOP => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '%s comment short description must end with a full stop',
        ],
        self::SPACING_AFTER => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Additional blank lines found at end of %s comment',
        ],
        self::SEE_ORDER => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The @see tag is in the wrong order; the tag precedes @return',
        ],
        self::EMPTY_SEE => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Content missing for @see tag in %s comment',
        ],
        self::SEE_INDENT => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '@see tag indented incorrectly; expected 1 spaces but found %s',
        ],
        self::DUPLICATE_RETURN => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Only 1 @return tag is allowed in function comment',
        ],
        self::MISSING_PARAM_TAG => [self::LEVEL => self::ERROR, self::MESSAGE => 'Doc comment for "%s" missing'],
        self::SPACING_AFTER_LONG_NAME => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Expected 1 space after the longest variable name',
        ],
        self::SPACING_AFTER_LONG_TYPE => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Expected 1 space after the longest type',
        ],
        self::MISSING_PARAM_TYPE => [self::LEVEL => self::ERROR, self::MESSAGE => 'Missing type at position %s'],
        self::MISSING_PARAM_NAME => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Missing parameter name at position %s',
        ],
        self::EXTRA_PARAM_COMMENT => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Superfluous doc comment at position %s',
        ],
        self::PARAM_NAME_NO_MATCH => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Doc comment for var %s does not match actual variable name %s at position %s',
        ],
        self::PARAM_NAME_NO_CASE_MATCH => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Doc comment for var %s does not match case of actual variable name %s at position %s',
        ],
        self::INVALID_TYPE_HINT => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Unknown type hint "%s" found for %s at position %s',
        ],
        self::INCORRECT_TYPE_HINT => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Expected type hint "%s"; found "%s" for %s at position %s',
        ],
        self::TYPE_HINT_MISSING => [
            self::LEVEL => self::INFO,
            self::MESSAGE => 'Type hint "%s" missing for %s at position %s',
        ],
        self::INCORRECT_PARAM_VAR_NAME => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Expected "%s"; found "%s" for %s at position %s',
        ],
        self::RETURN_ORDER => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The @return tag is in the wrong order; the tag follows @see (if used)',
        ],
        self::MISSING_RETURN_TYPE => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Return type missing for @return tag in function comment',
        ],
        self::INVALID_RETURN => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Function return type "%s" is invalid',
        ],
        self::INVALID_RETURN_VOID => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Function return type is void, but function contains return statement',
        ],
        self::INVALID_NO_RETURN => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Function return type is not void, but function has no return statement',
        ],
        self::INVALID_RETURN_NOT_VOID => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Function return type is not void, but function is returning void here',
        ],
        self::INCORRECT_INHERIT_DOC => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The incorrect inherit doc tag usage. Should be {@inheritdoc}',
        ],
        self::RETURN_INDENT => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '@return tag indented incorrectly; expected 1 space but found %s',
        ],
        self::MISSING_RETURN => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Missing @return tag in function comment',
        ],
        self::RETURN_NOT_REQUIRED => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => '@return tag is not required for constructor and destructor',
        ],
        self::INVALID_THROWS => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Exception type and comment missing for @throws tag in function comment',
        ],
        self::THROWS_NOT_CAPITAL => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => '@throws tag comment must start with a capital letter',
        ],
        self::THROWS_ORDER => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The @throws tag is in the wrong order; the tag follows @return',
        ],
        self::EMPTY_THROWS => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Comment missing for @throws tag in function comment',
        ],
        self::THROWS_NO_FULL_STOP => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '@throws tag comment must end with a full stop',
        ],
        self::SPACING_AFTER_PARAMS => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Last parameter comment requires a blank newline after it',
        ],
        self::SPACING_BEFORE_PARAMS => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Parameters must appear immediately after the comment',
        ],
        self::SPACING_BEFORE_PARAM_TYPE => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Expected 1 space before variable type',
        ],
        self::LONG_NOT_CAPITAL => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => '%s comment long description must start with a capital letter',
        ],
        self::TAG_NOT_ALLOWED => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => '@%s tag is not allowed in variable comment',
        ],
        self::DUPLICATE_VAR => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Only 1 @var tag is allowed in variable comment',
        ],
        self::VAR_ORDER => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'The @var tag must be the first tag in a variable comment',
        ],
        self::MISSING_VAR_TYPE => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Var type missing for @var tag in variable comment',
        ],
        self::INCORRECT_VAR_TYPE => [
            self::LEVEL => self::ERROR,
            self::MESSAGE => 'Expected "%s"; found "%s" for @var tag in variable comment',
        ],
        self::VAR_INDENT => [
            self::LEVEL => self::OFF,
            self::MESSAGE => '@var tag indented incorrectly; expected 1 space but found %s',
        ],
        self::MISSING_VAR => [
            self::LEVEL => self::WARNING,
            self::MESSAGE => 'Missing @var tag in variable comment',
        ],
        self::MISSING_PARAM_COMMENT => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Missing comment for param "%s" at position %s',
        ],
        self::PARAM_COMMENT_NOT_CAPITAL => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Param comment must start with a capital letter',
        ],
        self::PARAM_COMMENT_FULL_STOP => [
            self::LEVEL => self::OFF,
            self::MESSAGE => 'Param comment must end with a full stop',
        ],
    ];

    /**
     * List of allowed types
     *
     * @var string[]
     */
    protected static $allowedTypes = [
        'array',
        'boolean',
        'bool',
        'float',
        'integer',
        'int',
        'object',
        'string',
        'resource',
        'callable',
        'true',
        'false',
    ];

    /**
     * The current PHP_CodeSniffer_File object we are processing.
     *
     * @var PHP_CodeSniffer_File
     */
    protected $currentFile = null;

    /**
     * Constructor for class.
     *
     * @param PHP_CodeSniffer_File $phpcsFile
     */
    public function __construct(PHP_CodeSniffer_File $phpcsFile)
    {
        $this->currentFile = $phpcsFile;
    }

    /**
     * Returns the current file object
     *
     * @return PHP_CodeSniffer_File
     */
    public function getCurrentFile()
    {
        return $this->currentFile;
    }

    /**
     * Returns the eol character used in the file
     *
     * @return string
     */
    public function getEolChar()
    {
        return $this->currentFile->eolChar;
    }

    /**
     * Returns the array of allowed types for magento standard
     *
     * @return string[]
     */
    public function getAllowedTypes()
    {
        return self::$allowedTypes;
    }

    /**
     * This method will add the message as an error or warning depending on the configuration
     *
     * @param int    $stackPtr The stack position where the error occurred.
     * @param string $code     A violation code unique to the sniff message.
     * @param string[] $data     Replacements for the error message.
     * @param int    $severity The severity level for this error. A value of 0
     * @return void
     */
    public function addMessage($stackPtr, $code, $data = [], $severity = 0)
    {
        // Does the $code key exist in the report level
        if (array_key_exists($code, self::$reportingLevel)) {
            $message = self::$reportingLevel[$code][self::MESSAGE];
            $level = self::$reportingLevel[$code][self::LEVEL];
            if ($level === self::WARNING || $level === self::INFO || $level === self::OFF) {
                $s = $level;
                if ($severity !== 0) {
                    $s = $severity;
                }
                $this->currentFile->addWarning($message, $stackPtr, $code, $data, $s);
            } else {
                $this->currentFile->addError($message, $stackPtr, $code, $data, $severity);
            }
        }
    }

    /**
     * Returns if we should filter a particular file
     *
     * @return bool
     */
    public function shouldFilter()
    {
        $shouldFilter = false;
        $filename = $this->getCurrentFile()->getFilename();
        if (preg_match('#(?:/|\\\\)dev(?:/|\\\\)tests(?:/|\\\\)#', $filename)) {
            // TODO: Temporarily blacklist anything in dev/tests until a sweep of dev/tests can be made.
            //       This block of the if should be removed leaving only the phtml condition when dev/tests is swept.
            // Skip all dev tests files
            $shouldFilter = true;
        } elseif (preg_match('#(?:/|\\\\)Test(?:/|\\\\)Unit(?:/|\\\\)#', $filename)) {
            $shouldFilter = true;
        } elseif (preg_match('/\\.phtml$/', $filename)) {
            // Skip all phtml files
            $shouldFilter = true;
        }

        return $shouldFilter;
    }

    /**
     * Determine if text is a class name
     *
     * @param string $class
     * @return bool
     */
    protected function isClassName($class)
    {
        $return = false;
        if (preg_match('/^\\\\?[A-Z]\\w+(?:\\\\\\w+)*?$/', $class)) {
            $return = true;
        }
        return $return;
    }

    /**
     * Determine if the text has an ambiguous type
     *
     * @param string $text
     * @param array &$matches Type that was detected as ambiguous is in result.
     * @return bool
     */
    public function isAmbiguous($text, &$matches = [])
    {
        return preg_match('/(array|mixed)/', $text, $matches);
    }

    /**
     * Take the type and suggest the correct one.
     *
     * @param string $type
     * @return string
     */
    public function suggestType($type)
    {
        $suggestedName = null;
        // First check to see if this type is a list of types. If so we break it up and check each
        if (preg_match('/^.*?(?:\|.*)+$/', $type)) {
            // Return list of all types in this string.
            $types = explode('|', $type);
            if (is_array($types)) {
                // Loop over all types and call this method on each.
                $suggestions = [];
                foreach ($types as $t) {
                    $suggestions[] = $this->suggestType($t);
                }
                // Now that we have suggestions put them back together.
                $suggestedName = implode('|', $suggestions);
            } else {
                $suggestedName = 'Unknown';
            }
        } elseif ($this->isClassName($type)) {
            // If this looks like a class name.
            $suggestedName = $type;
        } else {
            // Only one type First check if that type is a base one.
            $lowerVarType = strtolower($type);
            if (in_array($lowerVarType, self::$allowedTypes)) {
                $suggestedName = $lowerVarType;
            }
            // If no name suggested yet then call the phpcs version of this method.
            if (empty($suggestedName)) {
                $suggestedName = PHP_CodeSniffer::suggestType($type);
            }
        }
        return $suggestedName;
    }
}
