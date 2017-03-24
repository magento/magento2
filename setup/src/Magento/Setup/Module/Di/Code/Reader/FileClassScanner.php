<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

class FileClassScanner
{
    /**
     * The filename of the file to introspect
     *
     * @var string
     */

    protected $filename;

    /**
     * The list of classes found in the file.
     *
     * @var bool
     */

    protected $classNames = false;

    /**
     * Constructor for the file class scanner.  Requires the filename
     * @param $filename
     */

    public function __construct( $filename )
    {
        $filename = realpath($filename);
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
        return file_get_contents($this->filename);
    }

    /**
     * Extracts the fully qualified class name from a file.  It only searches for the first match and stops looking
     * as soon as it enters the class definition itself.
     *
     * @return array
     */

    protected function extract()
    {
        $classes = [];
        $tokens = token_get_all($this->getFileContents());
        $namespace = '';
        $class = '';
        $triggerClass = false;
        $triggerNamespace = false;
        foreach ($tokens as $key => $token) {

            // The namespace keyword was found in the last loop
            if ($triggerNamespace) {
                if (is_array($token)) {
                    $namespace .= $token[1];
                } else {
                    $triggerNamespace = false;
                    $namespace .= '\\';
                    continue;
                }
            // The class keyword was found in the last loop
            } else if ($triggerClass && $token[0] == T_STRING) {
                $triggerClass = false;
                $class = $token[1];
            }

            // Current loop contains the namespace keyword.  Between this and the semicolon is the namespace
            if ($token[0] == T_NAMESPACE) {
                $triggerNamespace = true;
            // Current loop contains the class keyword.  Next loop will have the class name itself.
            } else if ($token[0] == T_CLASS ) {
                $triggerClass = true;
            }

            // We have a class name, let's concatenate and store it!
            if ($class != '' ) {
                $namespace = trim($namespace);
                $fqClassName = $namespace . trim($class);
                $classes[] = $fqClassName;
                return $classes;
            }
        }
        return [];
    }

    /**
     * Retrieves the first class found in a class file.  The return value is in an array format so it retains the
     * same usage as the FileScanner.
     *
     * @return array
     */

    public function getClassNames()
    {
        if ($this->classNames === false) {
            $this->classNames = $this->extract();
        }
        return $this->classNames;
    }

}
