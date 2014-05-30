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
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Version.php 22763 2010-08-02 02:59:36Z ramon $
 */

#require_once 'Zend/Tool/Framework/Registry.php';
#require_once 'Zend/Tool/Framework/Provider/Interface.php';
#require_once 'Zend/Version.php';

/**
 * Version Provider
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_System_Provider_Version
    implements Zend_Tool_Framework_Provider_Interface, Zend_Tool_Framework_Registry_EnabledInterface
{

    /**
     * @var Zend_Tool_Framework_Registry_Interface
     */
    protected $_registry = null;

    const MODE_MAJOR = 'major';
    const MODE_MINOR = 'minor';
    const MODE_MINI  = 'mini';

    protected $_specialties = array('MajorPart', 'MinorPart', 'MiniPart');

    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Show Action
     *
     * @param string $mode The mode switch can be one of: major, minor, or mini (default)
     * @param bool $nameIncluded
     */
    public function show($mode = self::MODE_MINI, $nameIncluded = true)
    {

        $versionInfo = $this->_splitVersion();

        switch($mode) {
            case self::MODE_MINOR:
                unset($versionInfo['mini']);
                break;
            case self::MODE_MAJOR:
                unset($versionInfo['mini'], $versionInfo['minor']);
                break;
        }

        $output = implode('.', $versionInfo);

        if ($nameIncluded) {
            $output = 'Zend Framework Version: ' . $output;
        }

        $this->_registry->response->appendContent($output);
    }

    public function showMajorPart($nameIncluded = true)
    {
        $versionNumbers = $this->_splitVersion();
        $output = (($nameIncluded == true) ? 'ZF Major Version: ' : null) . $versionNumbers['major'];
        $this->_registry->response->appendContent($output);
    }

    public function showMinorPart($nameIncluded = true)
    {
        $versionNumbers = $this->_splitVersion();
        $output = (($nameIncluded == true) ? 'ZF Minor Version: ' : null) . $versionNumbers['minor'];
        $this->_registry->response->appendContent($output);
    }

    public function showMiniPart($nameIncluded = true)
    {
        $versionNumbers = $this->_splitVersion();
        $output = (($nameIncluded == true) ? 'ZF Mini Version: ' : null)  . $versionNumbers['mini'];
        $this->_registry->response->appendContent($output);
    }

    protected function _splitVersion()
    {
        list($major, $minor, $mini) = explode('.', Zend_Version::VERSION);
        return array('major' => $major, 'minor' => $minor, 'mini' => $mini);
    }

}
