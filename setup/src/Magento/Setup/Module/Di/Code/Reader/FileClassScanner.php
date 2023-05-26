<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Setup\Module\Di\Code\Reader;

/**
 * Class FileClassScanner
 */
class FileClassScanner
{
    private const NAMESPACE_TOKENS = [
        T_WHITESPACE => true,
        T_STRING => true,
        T_NS_SEPARATOR => true,
        // PHP 8 compatibility.
        T_NAME_QUALIFIED => true,
        T_NAME_FULLY_QUALIFIED => true,
    ];

    private const ALLOWED_OPEN_BRACES_TOKENS = [
        T_CURLY_OPEN => true,
        T_DOLLAR_OPEN_CURLY_BRACES => true,
        T_STRING_VARNAME => true
    ];

    /**
     * The filename of the file to introspect
     *
     * @var string
     */
    private $filename;

    /**
     * The class name found in the file.
     *
     * @var string|bool
     */
    private $className = false;

    /**
     * @var array
     */
    private $tokens;

    /**
     * Constructor for the file class scanner.  Requires the filename
     *
     * @param string $filename
     */
    public function __construct($filename)
    {
        // phpcs:ignore
        $filename = realpath($filename);
        // phpcs:ignore
        if (!file_exists($filename) || !\is_file($filename)) {
            throw new InvalidFileException(
                sprintf(
                    'The file "%s" does not exist or is not a file',
                    $filename
                )
            );
        }
        $this->filename = $filename;
    }

    /**
     * Retrieves the contents of a file.  Mostly here for Mock injection
     *
     * @return string
     */
    public function getFileContents()
    {
        // phpcs:ignore
        return file_get_contents($this->filename);
    }

    /**
     * Retrieves the first class found in a file.
     *
     * @return string
     */
    public function getClassName(): string
    {
        if ($this->className === false) {
            $this->className = $this->extract();
        }
        return $this->className;
    }

    /**
     * Extracts the fully qualified class name from a file.
     *
     * It only searches for the first match and stops looking as soon as it enters the class definition itself.
     *
     * Warnings are suppressed for this method due to a micro-optimization that only really shows up when this logic
     * is called several millions of times, which can happen quite easily with even moderately sized codebases.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return string
     */
    private function extract(): string
    {
        $namespaceParts = [];
        $class = '';
        $triggerClass = false;
        $triggerNamespace = false;
        $braceLevel = 0;
        $bracedNamespace = false;
        $namespace = '';

        // phpcs:ignore
        $this->tokens = token_get_all($this->getFileContents());
        foreach ($this->tokens as $index => $token) {
            $tokenIsArray = is_array($token);
            // Is either a literal brace or an interpolated brace with a variable
            if ($token === '{' || ($tokenIsArray && isset(self::ALLOWED_OPEN_BRACES_TOKENS[$token[0]]))) {
                $braceLevel++;
            } elseif ($token === '}') {
                $braceLevel--;
            }
            // The namespace keyword was found in the last loop
            if ($triggerNamespace) {
                // A string ; or a discovered namespace that looks like "namespace name { }"
                if (!$tokenIsArray || ($namespaceParts && $token[0] === T_WHITESPACE)) {
                    $triggerNamespace = false;
                    $namespaceParts[] = '\\';
                    continue;
                }
                $namespaceParts[] = $token[1];

            // `class` token is not used with a valid class name
            } elseif ($triggerClass && !$tokenIsArray) {
                $triggerClass = false;
            // The class keyword was found in the last loop
            } elseif ($triggerClass && $token[0] === T_STRING) {
                $triggerClass = false;
                $class = $token[1];
            }

            switch ($token[0]) {
                case T_NAMESPACE:
                    // Current loop contains the namespace keyword. Between this and the semicolon is the namespace
                    $triggerNamespace = true;
                    $namespaceParts = [];
                    $bracedNamespace = $this->isBracedNamespace($index);
                    break;

                // PHP 8
                case T_NAME_QUALIFIED:
                case T_NAME_FULLY_QUALIFIED:
                    if ($triggerNamespace) {
                        $namespace = $token[1];
                    }
                    break;

                case T_TRAIT:
                case T_CLASS:
                    // Current loop contains the class keyword. Next loop will have the class name itself.
                    if ($braceLevel === 0 || ($bracedNamespace && $braceLevel === 1)) {
                        $triggerClass = true;
                    }
                    break;
            }

            // We have a class name, let's concatenate and return it!
            if ($class !== '') {
                $fqClassName = $namespace ? (trim($namespace) . '\\' . trim($class))
                    : (trim(implode('', $namespaceParts)) . trim($class));
                return $fqClassName;
            }
        }
        return $class;
    }

    /**
     * Looks forward from the current index to determine if the namespace is nested in {} or terminated with ;
     *
     * @param integer $index
     * @return bool
     */
    private function isBracedNamespace($index)
    {
        $len = count($this->tokens);
        while ($index++ < $len) {
            if (!is_array($this->tokens[$index])) {
                if ($this->tokens[$index] === ';') {
                    return false;
                } elseif ($this->tokens[$index] === '{') {
                    return true;
                }
                continue;
            }

            if (!isset(self::NAMESPACE_TOKENS[$this->tokens[$index][0]])) {
                throw new InvalidFileException('Namespace not defined properly');
            }
        }
        throw new InvalidFileException('Could not find namespace termination');
    }
}
