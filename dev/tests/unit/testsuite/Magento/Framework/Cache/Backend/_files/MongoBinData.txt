<?php
/**
 * {license_notice}
 *
 * @copyright   {copyright}
 * @license     {license_link}
 */
if (!extension_loaded('mongo')) {
    /**
     * \MongoBinData fake class to let unit tests go even without mongo extension
     * All the comments copied from documentation
     * @see http://php.net/manual/en/class.mongobindata.php for reference to real \MongoBinData class
     */
    class MongoBinData
    {
        /**
         * Array of bytes
         */
        const BYTE_ARRAY = 2;

        /**
         * @var $bin
         */
        public $bin;
    }
}
