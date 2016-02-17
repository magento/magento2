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
 * @subpackage Framework
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
#require_once 'Zend/Tool/Project/Provider/Abstract.php';

/**
 * @see Zend_Tool_Framework_Provider_Interactable
 */
#require_once 'Zend/Tool/Framework/Provider/Interactable.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Project_Provider_DbAdapter
    extends Zend_Tool_Project_Provider_Abstract
    implements Zend_Tool_Framework_Provider_Interactable, Zend_Tool_Framework_Provider_Pretendable
{

    protected $_appConfigFilePath = null;

    protected $_config = null;

    protected $_sectionName = 'production';

    public function configure($dsn = null, /* $interactivelyPrompt = false, */ $sectionName = 'production')
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);

        $appConfigFileResource = $profile->search('applicationConfigFile');

        if ($appConfigFileResource == false) {
            throw new Zend_Tool_Project_Exception('A project with an application config file is required to use this provider.');
        }

        $this->_appConfigFilePath = $appConfigFileResource->getPath();

        $this->_config = new Zend_Config_Ini($this->_appConfigFilePath, null, array('skipExtends' => true, 'allowModifications' => true));

        if ($sectionName != 'production') {
            $this->_sectionName = $sectionName;
        }

        if (!isset($this->_config->{$this->_sectionName})) {
            throw new Zend_Tool_Project_Exception('The config does not have a ' . $this->_sectionName . ' section.');
        }

        if (isset($this->_config->{$this->_sectionName}->resources->db)) {
            throw new Zend_Tool_Project_Exception('The config already has a db resource configured in section ' . $this->_sectionName . '.');
        }

        if ($dsn) {
            $this->_configureViaDSN($dsn);
        //} elseif ($interactivelyPrompt) {
        //    $this->_promptForConfig();
        } else {
            $this->_registry->getResponse()->appendContent('Nothing to do!');
        }


    }

    protected function _configureViaDSN($dsn)
    {
        $dsnVars = array();

        if (strpos($dsn, '=') === false) {
            throw new Zend_Tool_Project_Provider_Exception('At least one name value pair is expected, typcially '
                . 'in the format of "adapter=Mysqli&username=uname&password=mypass&dbname=mydb"'
                );
        }

        parse_str($dsn, $dsnVars);

        // parse_str suffers when magic_quotes is enabled
        if (get_magic_quotes_gpc()) {
            array_walk_recursive($dsnVars, array($this, '_cleanMagicQuotesInValues'));
        }

        $dbConfigValues = array('resources' => array('db' => null));

        if (isset($dsnVars['adapter'])) {
            $dbConfigValues['resources']['db']['adapter'] = $dsnVars['adapter'];
            unset($dsnVars['adapter']);
        }

        $dbConfigValues['resources']['db']['params'] = $dsnVars;

        $isPretend = $this->_registry->getRequest()->isPretend();

        // get the config resource
        $applicationConfig = $this->_loadedProfile->search('ApplicationConfigFile');
        $applicationConfig->addItem($dbConfigValues, $this->_sectionName, null);

        $response = $this->_registry->getResponse();

        if ($isPretend) {
            $response->appendContent('A db configuration for the ' . $this->_sectionName
                . ' section would be written to the application config file with the following contents: '
                );
            $response->appendContent($applicationConfig->getContents());
        } else {
            $applicationConfig->create();
            $response->appendContent('A db configuration for the ' . $this->_sectionName
                . ' section has been written to the application config file.'
                );
        }
    }

    protected function _cleanMagicQuotesInValues(&$value, $key)
    {
        $value = stripslashes($value);
    }

}
