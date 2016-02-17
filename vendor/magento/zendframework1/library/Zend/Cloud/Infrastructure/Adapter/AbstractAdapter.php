<?php
/**
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
 * @package    Zend_Cloud
 * @subpackage DocumentService
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

#require_once 'Zend/Cloud/Infrastructure/Adapter.php';
#require_once 'Zend/Cloud/Infrastructure/Instance.php';

/**
 * Abstract infrastructure service adapter
 *
 * @category   Zend
 * @package    Zend_Cloud_Infrastructure
 * @subpackage Adapter
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Cloud_Infrastructure_Adapter_AbstractAdapter implements Zend_Cloud_Infrastructure_Adapter
{
    /**
     * Store the last response from the adpter
     *
     * @var array
     */
    protected $adapterResult;

    /**
     * Valid metrics for monitor
     *
     * @var array
     */
    protected $validMetrics = array(
        Zend_Cloud_Infrastructure_Instance::MONITOR_CPU,
        Zend_Cloud_Infrastructure_Instance::MONITOR_RAM,
        Zend_Cloud_Infrastructure_Instance::MONITOR_DISK,
        Zend_Cloud_Infrastructure_Instance::MONITOR_DISK_READ,
        Zend_Cloud_Infrastructure_Instance::MONITOR_DISK_WRITE,
        Zend_Cloud_Infrastructure_Instance::MONITOR_NETWORK_IN,
        Zend_Cloud_Infrastructure_Instance::MONITOR_NETWORK_OUT,
    );

    /**
     * Get the last result of the adapter
     *
     * @return array
     */
    public function getAdapterResult()
    {
        return $this->adapterResult;
    }

    /**
     * Wait for status $status with a timeout of $timeout seconds
     *
     * @param  string $id
     * @param  string $status
     * @param  integer $timeout
     * @return boolean
     */
    public function waitStatusInstance($id, $status, $timeout = self::TIMEOUT_STATUS_CHANGE)
    {
        if (empty($id) || empty($status)) {
            return false;
        }

        $num = 0;
        while (($num<$timeout) && ($this->statusInstance($id) != $status)) {
            sleep(self::TIME_STEP_STATUS_CHANGE);
            $num += self::TIME_STEP_STATUS_CHANGE;
        }
        return ($num < $timeout);
    }

    /**
     * Run arbitrary shell script on an instance
     *
     * @param  string $id
     * @param  array $param
     * @param  string|array $cmd
     * @return string|array
     */
    public function deployInstance($id, $params, $cmd)
    {
        if (!function_exists("ssh2_connect")) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('Deployment requires the PHP "SSH" extension (ext/ssh2)');
        }

        if (empty($id)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the instance where to deploy');
        }

        if (empty($cmd)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the shell commands to run on the instance');
        }

        if (empty($params)
            || empty($params[Zend_Cloud_Infrastructure_Instance::SSH_USERNAME])
            || (empty($params[Zend_Cloud_Infrastructure_Instance::SSH_PASSWORD])
                && empty($params[Zend_Cloud_Infrastructure_Instance::SSH_KEY]))
        ) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('You must specify the params for the SSH connection');
        }

        $host = $this->publicDnsInstance($id);
        if (empty($host)) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception(sprintf(
                'The instance identified by "%s" does not exist',
                $id
            ));
        }

        $conn = ssh2_connect($host);
        if (!ssh2_auth_password($conn, $params[Zend_Cloud_Infrastructure_Instance::SSH_USERNAME],
                $params[Zend_Cloud_Infrastructure_Instance::SSH_PASSWORD])) {
            #require_once 'Zend/Cloud/Infrastructure/Exception.php';
            throw new Zend_Cloud_Infrastructure_Exception('SSH authentication failed');
        }

        if (is_array($cmd)) {
            $result = array();
            foreach ($cmd as $command) {
                $stream      = ssh2_exec($conn, $command);
                $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

                stream_set_blocking($errorStream, true);
                stream_set_blocking($stream, true);

                $output = stream_get_contents($stream);
                $error  = stream_get_contents($errorStream);

                if (empty($error)) {
                    $result[$command] = $output;
                } else {
                    $result[$command] = $error;
                }
            }
        } else {
            $stream      = ssh2_exec($conn, $cmd);
            $result      = stream_set_blocking($stream, true);
            $errorStream = ssh2_fetch_stream($stream, SSH2_STREAM_STDERR);

            stream_set_blocking($errorStream, true);
            stream_set_blocking($stream, true);

            $output = stream_get_contents($stream);
            $error  = stream_get_contents($errorStream);

            if (empty($error)) {
                $result = $output;
            } else {
                $result = $error;
            }
        }
        return $result;
    }
}
