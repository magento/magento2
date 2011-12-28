<?php
/**
 * Doc block for this file
 */

/**
 * Doc block for this class
 */
class Normal
{
    /**
     * Some private property
     * @var bool
     */
    private $_privateProperty = false;

    /**
     * Some protected property
     * @var Another_Class|null
     */
    protected $_protectedProperty = null;

    /**
     * Some public property
     * @var bool|null
     */
    public $publicProperty = null;

    /**
     * Some private method.
     * With long description.
     *
     * @param string $inParam
     * @return string
     */
    private function _privateMethod($inParam)
    {
        return 'Hello, ' . $inParam . '!';
    }

    /**
     * Some protected method.
     *
     * @param string $inParam
     * @param int $outParam
     * @return string
     */
    protected function _protectedMethod($inParam, &$outParam)
    {
        $message = $this->_privateMethod($inParam);
        $outParam = self::_customCrc($message);
        return $message;
    }

    /**
     * Static protected method
     *
     * @param string $string
     * return int
     */
    static protected function _customCrc($string)
    {
        return crc32($string);
    }

    /**
     * Public getter.
     *
     * @return Another_Class|null
     */
    public function publicMethod()
    {
        return $this->_protectedProperty;
    }
}
