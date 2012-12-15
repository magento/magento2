<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Validator
 */

namespace Zend\Validator\File;

/**
 * Validator for counting all words in a file
 *
 * @category  Zend
 * @package   Zend_Validate
 */
class WordCount extends Count
{
    /**
     * @const string Error constants
     */
    const TOO_MUCH  = 'fileWordCountTooMuch';
    const TOO_LESS  = 'fileWordCountTooLess';
    const NOT_FOUND = 'fileWordCountNotFound';

    /**
     * @var array Error message templates
     */
    protected $messageTemplates = array(
        self::TOO_MUCH => "Too much words, maximum '%max%' are allowed but '%count%' were counted",
        self::TOO_LESS => "Too less words, minimum '%min%' are expected but '%count%' were counted",
        self::NOT_FOUND => "File '%value%' is not readable or does not exist",
    );

    /**
     * Returns true if and only if the counted words are at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string $value Filename to check for word count
     * @param  array  $file  File data from \Zend\File\Transfer\Transfer
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        if ($file === null) {
            $file = array('name' => basename($value));
        }

        // Is file readable ?
        if (false === stream_resolve_include_path($value)) {
            return $this->throwError($file, self::NOT_FOUND);
        }

        $content = file_get_contents($value);
        $this->count = str_word_count($content);
        if (($this->getMax() !== null) && ($this->count > $this->getMax())) {
            return $this->throwError($file, self::TOO_MUCH);
        }

        if (($this->getMin() !== null) && ($this->count < $this->getMin())) {
            return $this->throwError($file, self::TOO_LESS);
        }

        return true;
    }
}
