<?php

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Module\Di\Code\Reader;

class FileClassScanner
{

    protected $filename;
    protected $classNames = false;

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

    public function getFileContents()
    {
        return file_get_contents($this->filename);
    }

    protected function extract()
    {
        $classes = [];
        $tokens = token_get_all($this->getFileContents());
        $namespace = '';
        $class = '';
        $triggerClass = false;
        $triggerNamespace = false;
        $paramNestingLevel = $currentParamNestingLevel = 0;
        foreach ($tokens as $key => $token) {
            if (!is_array($token)) {
                if ($token == '{') {
                    $paramNestingLevel++;
                } else if ($token == '}') {
                    $paramNestingLevel--;
                }
            }
            if ($triggerNamespace) {
                if (is_array($token)) {
                    $namespace .= $token[1];
                } else {
                    $currentParamNestingLevel = $paramNestingLevel;
                    $triggerNamespace = false;
                    $namespace .= '\\';
                    continue;
                }
            } else if ($triggerClass && $token[0] == T_STRING) {
                $triggerClass = false;
                $class = $token[1];
            }
            if ($token[0] == T_NAMESPACE) {
                $triggerNamespace = true;
            } else if ($token[0] == T_CLASS && $currentParamNestingLevel == $paramNestingLevel) {
                $triggerClass = true;
            }
            if ($class != '' && $currentParamNestingLevel == $paramNestingLevel) {
                $namespace = trim($namespace);
                $fqClassName = $namespace . trim($class);
                $classes[] = $fqClassName;
                $class = '';
                continue;
            }
        }
        return $classes;
    }

    public function getClassNames()
    {
        if ($this->classNames === false) {
            $this->classNames = $this->extract();
        }
        return $this->classNames;
    }


}
