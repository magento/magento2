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
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
 */
#require_once 'Zend/Service/WindowsAzure/Management/ServiceEntityAbstract.php';

/**
 * @category   Zend
 * @package    Zend_Service_WindowsAzure
 * @subpackage Management
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @property string $CertificateUrl          Certificate thumbprint address.
 * @property string $Thumbprint              Certificate thumbprint.
 * @property string $ThumbprintAlgorithm	 Certificate thumbprint algorithm.
 * @property string $Data                    Certificate data.
 */
class Zend_Service_WindowsAzure_Management_CertificateInstance
	extends Zend_Service_WindowsAzure_Management_ServiceEntityAbstract
{
    /**
     * Constructor
     *
     * @param string $certificateUrl          Certificate thumbprint address.
     * @param string $thumbprint              Certificate thumbprint.
     * @param string $thumbprintAlgorithm	 Certificate thumbprint algorithm.
     * @param string $data                    Certificate data.
	 */
    public function __construct($certificateUrl, $thumbprint, $thumbprintAlgorithm, $data)
    {
        $this->_data = array(
            'certificateurl'       => $certificateUrl,
            'thumbprint'           => $thumbprint,
            'thumbprintalgorithm'  => $thumbprintAlgorithm,
            'data'                 => base64_decode($data)
        );
    }
}
