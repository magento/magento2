<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Magento
 * @package     Magento_Connect
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class config
 *
 * @category   Magento
 * @package    Magento_Connect
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Downloader\Model\Config;

class AbstractConfig extends \Magento\Downloader\Model
{
    /**
    * Retrieve file name
    *
    * @return string
    */
    public function getFilename()
    {
        return $this->controller()->filepath('config.ini');
    }

    /**
    * Load file
    *
    * @return \Magento\Downloader\Model\Config
    */
    public function load()
    {
        if (!file_exists($this->getFilename())) {
            return $this;
        }
        $rows = file($this->getFilename());
        if (!$rows) {
            return $this;
        }
        foreach ($rows as $row) {
            $arr = explode('=', $row, 2);
            if (count($arr)!==2) {
                continue;
            }
            $key = trim($arr[0]);
            $value = trim($arr[1], " \t\"'\n\r");
            if (!$key || $key[0]=='#' || $key[0]==';') {
                continue;
            }
            $this->set($key, $value);
        }
        return $this;
    }

    /**
    * Save file
    *
    * @return \Magento\Downloader\Model\Config
    */
    public function save()
    {
        if ((!is_writable($this->getFilename())&&is_file($this->getFilename()))||(dirname($this->getFilename())!=''&&!is_writable(dirname($this->getFilename())))) {
            if(isset($this->_data['ftp'])&&!empty($this->_data['ftp'])&&strlen($this->get('downloader_path'))>0){
                $confFile=$this->get('downloader_path') . '/' . basename($this->getFilename());
                $ftpObj = new \Magento\Connect\Ftp();
                $ftpObj->connect($this->_data['ftp']);
                $tempFile = tempnam(sys_get_temp_dir(),'configini');
                $fp = fopen($tempFile, 'w');
                foreach ($this->_data as $k=>$v) {
                    fwrite($fp, $k.'='.$v."\n");
                }
                fclose($fp);
                $ret=$ftpObj->upload($confFile, $tempFile);
                $ftpObj->close();
            }else{
                /* @TODO: show Warning message*/
                $this->controller()->session()
                    ->addMessage('warning', 'Invalid file permissions, could not save configuration.');
                return $this;
            }
            /**/
        }else{
            $fp = fopen($this->getFilename(), 'w');
            foreach ($this->_data as $k=>$v) {
                fwrite($fp, $k.'='.$v."\n");
            }
            fclose($fp);
        }
        return $this;
    }

    /**
     * Return channel label for channel name
     *
     * @param string $channel
     * @return string
     */
    public function getChannelLabel($channel)
    {
        $channelLabel = '';
        switch($channel)
        {
            case 'community':
                $channelLabel = 'Magento Community Edition';
                break;
            default:
                $channelLabel = $channel;
                break;
        }
        return $channelLabel;
    }
}
?>
