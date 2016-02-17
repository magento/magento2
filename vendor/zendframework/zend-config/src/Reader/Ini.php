<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Reader;

use Zend\Config\Exception;

/**
 * INI config reader.
 */
class Ini implements ReaderInterface
{
    /**
     * Separator for nesting levels of configuration data identifiers.
     *
     * @var string
     */
    protected $nestSeparator = '.';

    /**
     * Directory of the file to process.
     *
     * @var string
     */
    protected $directory;

    /**
     * Set nest separator.
     *
     * @param  string $separator
     * @return self
     */
    public function setNestSeparator($separator)
    {
        $this->nestSeparator = $separator;
        return $this;
    }

    /**
     * Get nest separator.
     *
     * @return string
     */
    public function getNestSeparator()
    {
        return $this->nestSeparator;
    }

    /**
     * fromFile(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromFile()
     * @param  string $filename
     * @return array
     * @throws Exception\RuntimeException
     */
    public function fromFile($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }

        $this->directory = dirname($filename);

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error reading INI file "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );
        $ini = parse_ini_file($filename, true);
        restore_error_handler();

        return $this->process($ini);
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @param  string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return array();
        }
        $this->directory = null;

        set_error_handler(
            function ($error, $message = '') {
                throw new Exception\RuntimeException(
                    sprintf('Error reading INI string: %s', $message),
                    $error
                );
            },
            E_WARNING
        );
        $ini = parse_ini_string($string, true);
        restore_error_handler();

        return $this->process($ini);
    }

    /**
     * Process data from the parsed ini file.
     *
     * @param  array $data
     * @return array
     */
    protected function process(array $data)
    {
        $config = array();

        foreach ($data as $section => $value) {
            if (is_array($value)) {
                if (strpos($section, $this->nestSeparator) !== false) {
                    $sections = explode($this->nestSeparator, $section);
                    $config = array_merge_recursive($config, $this->buildNestedSection($sections, $value));
                } else {
                    $config[$section] = $this->processSection($value);
                }
            } else {
                $this->processKey($section, $value, $config);
            }
        }

        return $config;
    }

    /**
     * Process a nested section
     *
     * @param array $sections
     * @param mixed $value
     * @return array
     */
    private function buildNestedSection($sections, $value)
    {
        if (count($sections) == 0) {
            return $this->processSection($value);
        }

        $nestedSection = array();

        $first = array_shift($sections);
        $nestedSection[$first] = $this->buildNestedSection($sections, $value);

        return $nestedSection;
    }

    /**
     * Process a section.
     *
     * @param  array $section
     * @return array
     */
    protected function processSection(array $section)
    {
        $config = array();

        foreach ($section as $key => $value) {
            $this->processKey($key, $value, $config);
        }

        return $config;
    }

    /**
     * Process a key.
     *
     * @param  string $key
     * @param  string $value
     * @param  array  $config
     * @return array
     * @throws Exception\RuntimeException
     */
    protected function processKey($key, $value, array &$config)
    {
        if (strpos($key, $this->nestSeparator) !== false) {
            $pieces = explode($this->nestSeparator, $key, 2);

            if (!strlen($pieces[0]) || !strlen($pieces[1])) {
                throw new Exception\RuntimeException(sprintf('Invalid key "%s"', $key));
            } elseif (!isset($config[$pieces[0]])) {
                if ($pieces[0] === '0' && !empty($config)) {
                    $config = array($pieces[0] => $config);
                } else {
                    $config[$pieces[0]] = array();
                }
            } elseif (!is_array($config[$pieces[0]])) {
                throw new Exception\RuntimeException(
                    sprintf('Cannot create sub-key for "%s", as key already exists', $pieces[0])
                );
            }

            $this->processKey($pieces[1], $value, $config[$pieces[0]]);
        } else {
            if ($key === '@include') {
                if ($this->directory === null) {
                    throw new Exception\RuntimeException('Cannot process @include statement for a string config');
                }

                $reader  = clone $this;
                $include = $reader->fromFile($this->directory . '/' . $value);
                $config  = array_replace_recursive($config, $include);
            } else {
                $config[$key] = $value;
            }
        }
    }
}
