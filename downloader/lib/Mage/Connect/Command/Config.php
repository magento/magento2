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
 * @category    Mage
 * @package     Mage_Connect
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Configuration command callbacks
 *
 * @throws Exception
 * @category    Mage
 * @package     Mage_Connect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Connect_Command_Config extends Mage_Connect_Command
{
    /**
     * Parameters constants
     */
    const PARAM_KEY = 0;
    const PARAM_VAL = 1;

    /**
     * Show config variable
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doConfigShow($command, $options, $params)
    {
        $this->cleanupParams($params);

        try {
            $values = array();

            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($config,  $ftpObj) = $packager->getRemoteConfig($ftp);
            } else {
                $config = $this->config();
            }
            foreach( $config as $k=>$v ) {
                $values[$k] = $v;
            }
            if($ftp) {
                @unlink($config->getFilename());
            }
            $data = array($command  => array('data'=>$values));
            $this->ui()->output($data);
        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Set config variable
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doConfigSet($command, $options, $params)
    {
        $this->cleanupParams($params);

        try {
            if(count($params) < 2) {
                throw new Exception("Parameters count should be >= 2");
            }
            $key = strtolower($params[self::PARAM_KEY]);
            $val = strval($params[self::PARAM_VAL]);
            $packager = $this->getPackager();

            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if(!$ftp) {
                $config = $this->config();
                $ftp=$config->remote_config;
            }
            if($ftp) {
                list($cache, $config, $ftpObj) = $packager->getRemoteConf($ftp);
            }

            if(!$config->hasKey($key)) {
                throw new Exception ("No such config variable: {$key}!");
            }
            if(!$config->validate($key, $val)) {
                $possible = $this->config()->possible($key);
                $type = $this->config()->type($key);
                $errString = "Invalid value specified for $key!";
                throw new Exception($errString);
            }
            if($ftp) {
                $packager->writeToRemoteConfig($config, $ftpObj);
            }
            $this->config()->$key = $val;
            $this->ui()->output('Success');
        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Get config var
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doConfigGet($command, $options, $params)
    {
        $this->cleanupParams($params);

        try {
            if(count($params) < 1) {
                throw new Exception("Parameters count should be >= 1");
            }
            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($config,  $ftpObj) = $packager->getRemoteConfig($ftp);
            } else {
                $config = $this->config();
            }
            $key = strtolower($params[self::PARAM_KEY]);
            if(!$config->hasKey($key)) {
                throw new Exception("No such config variable '{$key}'!");
            }
            if($ftp) {
                @unlink($config->getFilename());
            }
            $this->ui()->output($config->$key);
        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }

    /**
     * Config help
     * @param string $command
     * @param array $options
     * @param array $params
     * @return void
     */
    public function doConfigHelp($command, $options, $params)
    {
        try {
            $this->cleanupParams($params);
            if(count($params) < 1) {
                throw new Exception( "Parameters count should be >= 1");
            }
            $packager = $this->getPackager();
            $ftp = empty($options['ftp']) ? false : $options['ftp'];
            if($ftp) {
                list($config,  $ftpObj) = $packager->getRemoteConfig($ftp);
            } else {
                $config = $this->config();
            }

            $key = strtolower($params[self::PARAM_KEY]);
            if(!$this->config()->hasKey($key)) {
                throw new Exception("No such config variable '{$key}'!");
            }

            $possible = $config->possible($key);
            $type = $config->type($key);
            $doc = $config->doc($key);
            if($ftp) {
                @unlink($config->getFilename());
            }
            $data = array();
            $data[$command]['data'] = array(
            'name' => array('Variable name', $key),
            'type' => array('Value type', $type),
            'possible' => array('Possible values', $possible),
            'doc' => $doc,
            );
            $this->ui()->output($data);
        } catch (Exception $e) {
            if($ftp) {
                @unlink($config->getFilename());
            }
            return $this->doError($command, $e->getMessage());
        }
    }
}
