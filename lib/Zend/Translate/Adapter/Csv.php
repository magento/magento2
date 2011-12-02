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
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id: Csv.php 21661 2010-03-27 20:20:27Z thomas $
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/** Zend_Locale */
#require_once 'Zend/Locale.php';

/** Zend_Translate_Adapter */
#require_once 'Zend/Translate/Adapter.php';


/**
 * @category   Zend
 * @package    Zend_Translate
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Translate_Adapter_Csv extends Zend_Translate_Adapter
{
    private $_data    = array();

    /**
     * Generates the adapter
     *
     * @param  array|Zend_Config $options Translation content
     */
    public function __construct($options = array())
    {
        $this->_options['delimiter'] = ";";
        $this->_options['length']    = 0;
        $this->_options['enclosure'] = '"';

        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        } else if (func_num_args() > 1) {
            $args               = func_get_args();
            $options            = array();
            $options['content'] = array_shift($args);

            if (!empty($args)) {
                $options['locale'] = array_shift($args);
            }

            if (!empty($args)) {
                $opt     = array_shift($args);
                $options = array_merge($opt, $options);
            }
        } else if (!is_array($options)) {
            $options = array('content' => $options);
        }

        parent::__construct($options);
    }

    /**
     * Load translation data
     *
     * @param  string|array  $filename  Filename and full path to the translation source
     * @param  string        $locale    Locale/Language to add data for, identical with locale identifier,
     *                                  see Zend_Locale for more information
     * @param  array         $option    OPTIONAL Options to use
     * @return array
     */
    protected function _loadTranslationData($filename, $locale, array $options = array())
    {
        $this->_data = array();
        $options     = $options + $this->_options;
        $this->_file = @fopen($filename, 'rb');
        if (!$this->_file) {
            #require_once 'Zend/Translate/Exception.php';
            throw new Zend_Translate_Exception('Error opening translation file \'' . $filename . '\'.');
        }

        while(($data = fgetcsv($this->_file, $options['length'], $options['delimiter'], $options['enclosure'])) !== false) {
            if (substr($data[0], 0, 1) === '#') {
                continue;
            }

            if (!isset($data[1])) {
                continue;
            }

            if (count($data) == 2) {
                $this->_data[$locale][$data[0]] = $data[1];
            } else {
                $singular = array_shift($data);
                $this->_data[$locale][$singular] = $data;
            }
        }

        return $this->_data;
    }

    /**
     * returns the adapters name
     *
     * @return string
     */
    public function toString()
    {
        return "Csv";
    }
}
