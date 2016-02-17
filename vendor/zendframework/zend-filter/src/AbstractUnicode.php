<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Filter;

abstract class AbstractUnicode extends AbstractFilter
{
    /**
     * Set the input encoding for the given string
     *
     * @param  string|null $encoding
     * @return self
     * @throws Exception\InvalidArgumentException
     * @throws Exception\ExtensionNotLoadedException
     */
    public function setEncoding($encoding = null)
    {
        if ($encoding !== null) {
            if (!function_exists('mb_strtolower')) {
                throw new Exception\ExtensionNotLoadedException(sprintf(
                    '%s requires mbstring extension to be loaded',
                    get_class($this)
                ));
            }

            $encoding    = strtolower($encoding);
            $mbEncodings = array_map('strtolower', mb_list_encodings());
            if (!in_array($encoding, $mbEncodings)) {
                throw new Exception\InvalidArgumentException(sprintf(
                    "Encoding '%s' is not supported by mbstring extension",
                    $encoding
                ));
            }
        }

        $this->options['encoding'] = $encoding;
        return $this;
    }

    /**
     * Returns the set encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        if ($this->options['encoding'] === null && function_exists('mb_internal_encoding')) {
            $this->options['encoding'] = mb_internal_encoding();
        }

        return $this->options['encoding'];
    }
}
