<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Validator\Barcode;

class Identcode extends AbstractAdapter
{
    /**
     * Allowed barcode lengths
     * @var int
     */
    protected $length = 12;

    /**
     * Allowed barcode characters
     * @var string
     */
    protected $characters = '0123456789';

    /**
     * Checksum function
     * @var string
     */
    protected $checksum = 'identcode';

    /**
     * Constructor for this barcode adapter
     */
    public function __construct()
    {
        $this->setLength(12);
        $this->setCharacters('0123456789');
        $this->setChecksum('identcode');
    }
}
