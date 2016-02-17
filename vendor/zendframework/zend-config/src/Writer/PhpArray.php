<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Writer;

use Zend\Config\Exception;

class PhpArray extends AbstractWriter
{
    /**
     * @var string
     */
    const INDENT_STRING = '    ';

    /**
     * @var bool
     */
    protected $useBracketArraySyntax = false;

    /**
     * processConfig(): defined by AbstractWriter.
     *
     * @param  array $config
     * @return string
     */
    public function processConfig(array $config)
    {
        $arraySyntax = array(
            'open' => $this->useBracketArraySyntax ? '[' : 'array(',
            'close' => $this->useBracketArraySyntax ? ']' : ')'
        );

        return "<?php\n" .
               "return " . $arraySyntax['open'] . "\n" . $this->processIndented($config, $arraySyntax) .
               $arraySyntax['close'] . ";\n";
    }

    /**
     * Sets whether or not to use the PHP 5.4+ "[]" array syntax.
     *
     * @param  bool $value
     * @return self
     */
    public function setUseBracketArraySyntax($value)
    {
        $this->useBracketArraySyntax = $value;
        return $this;
    }

    /**
     * toFile(): defined by Writer interface.
     *
     * @see    WriterInterface::toFile()
     * @param  string  $filename
     * @param  mixed   $config
     * @param  bool $exclusiveLock
     * @return void
     * @throws Exception\InvalidArgumentException
     * @throws Exception\RuntimeException
     */
    public function toFile($filename, $config, $exclusiveLock = true)
    {
        if (empty($filename)) {
            throw new Exception\InvalidArgumentException('No file name specified');
        }

        $flags = 0;
        if ($exclusiveLock) {
            $flags |= LOCK_EX;
        }

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error writing to "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );

        try {
            // for Windows, paths are escaped.
            $dirname = str_replace('\\', '\\\\', dirname($filename));

            $string  = $this->toString($config);
            $string  = str_replace("'" . $dirname, "__DIR__ . '", $string);

            file_put_contents($filename, $string, $flags);
        } catch (\Exception $e) {
            restore_error_handler();
            throw $e;
        }

        restore_error_handler();
    }

    /**
     * Recursively processes a PHP config array structure into a readable format.
     *
     * @param  array $config
     * @param  array $arraySyntax
     * @param  int   $indentLevel
     * @return string
     */
    protected function processIndented(array $config, array $arraySyntax, &$indentLevel = 1)
    {
        $arrayString = "";

        foreach ($config as $key => $value) {
            $arrayString .= str_repeat(self::INDENT_STRING, $indentLevel);
            $arrayString .= (is_int($key) ? $key : "'" . addslashes($key) . "'") . ' => ';

            if (is_array($value)) {
                if ($value === array()) {
                    $arrayString .= $arraySyntax['open'] . $arraySyntax['close'] . ",\n";
                } else {
                    $indentLevel++;
                    $arrayString .= $arraySyntax['open'] . "\n"
                                  . $this->processIndented($value, $arraySyntax, $indentLevel)
                                  . str_repeat(self::INDENT_STRING, --$indentLevel) . $arraySyntax['close'] . ",\n";
                }
            } elseif (is_object($value) || is_string($value)) {
                $arrayString .= var_export($value, true) . ",\n";
            } elseif (is_bool($value)) {
                $arrayString .= ($value ? 'true' : 'false') . ",\n";
            } elseif ($value === null) {
                $arrayString .= "null,\n";
            } else {
                $arrayString .= $value . ",\n";
            }
        }

        return $arrayString;
    }
}
