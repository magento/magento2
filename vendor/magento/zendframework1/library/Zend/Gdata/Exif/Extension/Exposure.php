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
 * @package    Zend_Gdata
 * @subpackage Exif
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Gdata_Extension
 */
#require_once 'Zend/Gdata/Extension.php';

/**
 * @see Zend_Gdata_Exif
 */
#require_once 'Zend/Gdata/Exif.php';

/**
 * Represents the exif:exposure element used by the Gdata Exif extensions.
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @subpackage Exif
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_Exif_Extension_Exposure extends Zend_Gdata_Extension
{

    protected $_rootNamespace = 'exif';
    protected $_rootElement = 'exposure';

    /**
     * Constructs a new Zend_Gdata_Exif_Extension_Exposure object.
     *
     * @param string $text (optional) The value to use for this element.
     */
    public function __construct($text = null)
    {
        $this->registerAllNamespaces(Zend_Gdata_Exif::$namespaces);
        parent::__construct();
        $this->setText($text);
    }

}
