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
 * @package    Zend_Service
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Imfs.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

/**
 * @see Zend_Service_Nirvanix_Namespace_Base
 */
#require_once 'Zend/Service/Nirvanix/Namespace/Base.php';

/**
 * Namespace proxy with additional convenience methods for the IMFS namespace.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Nirvanix
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Nirvanix_Namespace_Imfs extends Zend_Service_Nirvanix_Namespace_Base
{
    /**
     * Convenience function to get the contents of a file on
     * the Nirvanix IMFS.  Analog to PHP's file_get_contents().
     *
     * @param  string  $filePath    Remote path and filename
     * @param  integer $expiration  Number of seconds that Nirvanix
     *                              make the file available for download.
     * @return string               Contents of file
     */
    public function getContents($filePath, $expiration = 3600)
    {
        // get url to download the file
        $params = array('filePath'   => $filePath,
                        'expiration' => $expiration);
        $resp = $this->getOptimalUrls($params);
        $url = (string)$resp->Download->DownloadURL;

        // download the file
        $this->_httpClient->resetParameters();
        $this->_httpClient->setUri($url);
        $resp = $this->_httpClient->request(Zend_Http_Client::GET);

        return $resp->getBody();
    }

    /**
     * Convenience function to put the contents of a string into
     * the Nirvanix IMFS.  Analog to PHP's file_put_contents().
     *
     * @param  string  $filePath    Remote path and filename
     * @param  integer $data        Data to store in the file
     * @param  string  $mimeType    Mime type of data
     * @return Zend_Service_Nirvanix_Response
     */
    public function putContents($filePath, $data, $mimeType = null)
    {
        // get storage node for upload
        $params = array('sizeBytes' => strlen($data));
        $resp = $this->getStorageNode($params);
        $host        = (string)$resp->GetStorageNode->UploadHost;
        $uploadToken = (string)$resp->GetStorageNode->UploadToken;

        // http upload data into remote file
        $this->_httpClient->resetParameters();
        $this->_httpClient->setUri("http://{$host}/Upload.ashx");
        $this->_httpClient->setParameterPost('uploadToken', $uploadToken);
        $this->_httpClient->setParameterPost('destFolderPath', str_replace('\\', '/',dirname($filePath)));
        $this->_httpClient->setFileUpload(basename($filePath), 'uploadFile', $data, $mimeType);
        $response = $this->_httpClient->request(Zend_Http_Client::POST);

        return new Zend_Service_Nirvanix_Response($response->getBody());
    }

    /**
     * Convenience function to remove a file from the Nirvanix IMFS.
     * Analog to PHP's unlink().
     *
     * @param  string  $filePath  Remove path and filename
     * @return Zend_Service_Nirvanix_Response
     */
    public function unlink($filePath)
    {
        $params = array('filePath' => $filePath);
        return $this->deleteFiles($params);
    }

}
