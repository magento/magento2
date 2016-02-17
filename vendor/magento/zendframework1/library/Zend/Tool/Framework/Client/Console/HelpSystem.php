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
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Console_HelpSystem
{

    /**
     * @var Zend_Tool_Framework_Registry_Interface
     */
    protected $_registry = null;

    /**
     * @var Zend_Tool_Framework_Client_Response
     */
    protected $_response = null;

    /**
     * setRegistry()
     *
     * @param Zend_Tool_Framework_Registry_Interface $registry
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        $this->_response = $registry->getResponse();
        return $this;
    }

    /**
     * respondWithErrorMessage()
     *
     * @param string $errorMessage
     * @param Exception $exception
     */
    public function respondWithErrorMessage($errorMessage, Exception $exception = null)
    {
        // break apart the message into wrapped chunks
        $errorMessages = explode(PHP_EOL, wordwrap($errorMessage, 70, PHP_EOL, false));

        $text = 'An Error Has Occurred';
        $this->_response->appendContent($text, array('color' => array('hiWhite', 'bgRed'), 'aligncenter' => true));
        $this->_response->appendContent($errorMessage, array('indention' => 1, 'blockize' => 72, 'color' => array('white', 'bgRed')));

        if ($exception && $this->_registry->getRequest()->isDebug()) {
            $this->_response->appendContent($exception->getTraceAsString());
        }

        $this->_response->appendContent(null, array('separator' => true));
        return $this;
    }

    /**
     * respondWithGeneralHelp()
     *
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function respondWithGeneralHelp()
    {
        $this->_respondWithHeader();

        $noSeparator = array('separator' => false);

        $this->_response->appendContent('Usage:', array('color' => 'green'))
            ->appendContent('    ', $noSeparator)
            ->appendContent('zf', array_merge(array('color' => 'cyan'), $noSeparator))
            ->appendContent(' [--global-opts]', $noSeparator)
            ->appendContent(' action-name', array_merge(array('color' => 'cyan'), $noSeparator))
            ->appendContent(' [--action-opts]', $noSeparator)
            ->appendContent(' provider-name', array_merge(array('color' => 'cyan'), $noSeparator))
            ->appendContent(' [--provider-opts]', $noSeparator)
            ->appendContent(' [provider parameters ...]')
            ->appendContent('    Note: You may use "?" in any place of the above usage string to ask for more specific help information.', array('color'=>'yellow'))
            ->appendContent('    Example: "zf ? version" will list all available actions for the version provider.', array('color'=>'yellow', 'separator' => 2))
            ->appendContent('Providers and their actions:', array('color' => 'green'));

        $this->_respondWithSystemInformation();
        return $this;
    }

    /**
     * respondWithActionHelp()
     *
     * @param string $actionName
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function respondWithActionHelp($actionName)
    {
        $this->_respondWithHeader();
        $this->_response->appendContent('Providers that support the action "' . $actionName . '"', array('color' => 'green'));
        $this->_respondWithSystemInformation(null, $actionName);
        return $this;
    }

    /**
     * respondWithSpecialtyAndParamHelp()
     *
     * @param string $providerName
     * @param string $actionName
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function respondWithSpecialtyAndParamHelp($providerName, $actionName)
    {
        $this->_respondWithHeader();
        $this->_response->appendContent(
            'Details for action "' . $actionName . '" and provider "' . $providerName . '"',
            array('color' => 'green')
            );
        $this->_respondWithSystemInformation($providerName, $actionName, true);
        return $this;
    }

    /**
     * respondWithProviderHelp()
     *
     * @param string $providerName
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    public function respondWithProviderHelp($providerName)
    {
        $this->_respondWithHeader();
        $this->_response->appendContent('Actions supported by provider "' . $providerName . '"', array('color' => 'green'));
        $this->_respondWithSystemInformation($providerName);
        return $this;
    }

    /**
     * _respondWithHeader()
     *
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _respondWithHeader()
    {
        /**
         * @see Zend_Version
         */
        #require_once 'Zend/Version.php';
        $this->_response->appendContent('Zend Framework', array('color' => array('hiWhite'), 'separator' => false));
        $this->_response->appendContent(' Command Line Console Tool v' . Zend_Version::VERSION . '');
        return $this;
    }

    /**
     * _respondWithSystemInformation()
     *
     * @param string $providerNameFilter
     * @param string $actionNameFilter
     * @param bool $includeAllSpecialties
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _respondWithSystemInformation($providerNameFilter = null, $actionNameFilter = null, $includeAllSpecialties = false)
    {
        $manifest = $this->_registry->getManifestRepository();

        $providerMetadatasSearch = array(
            'type'       => 'Tool',
            'name'       => 'providerName',
            'clientName' => 'console'
            );

        if (is_string($providerNameFilter)) {
            $providerMetadatasSearch = array_merge($providerMetadatasSearch, array('providerName' => $providerNameFilter));
        }

        $actionMetadatasSearch = array(
            'type'       => 'Tool',
            'name'       => 'actionName',
            'clientName' => 'console'
            );

        if (is_string($actionNameFilter)) {
            $actionMetadatasSearch = array_merge($actionMetadatasSearch, array('actionName' => $actionNameFilter));
        }

        // get the metadata's for the things to display
        $displayProviderMetadatas = $manifest->getMetadatas($providerMetadatasSearch);
        $displayActionMetadatas = $manifest->getMetadatas($actionMetadatasSearch);

        // create index of actionNames
        for ($i = 0; $i < count($displayActionMetadatas); $i++) {
            $displayActionNames[] = $displayActionMetadatas[$i]->getActionName();
        }

        foreach ($displayProviderMetadatas as $providerMetadata) {

            $providerNameDisplayed = false;

            $providerName = $providerMetadata->getProviderName();
            $providerSignature = $providerMetadata->getReference();

            foreach ($providerSignature->getActions() as $actionInfo) {

                $actionName = $actionInfo->getName();

                // check to see if this action name is valid
                if (($foundActionIndex = array_search($actionName, $displayActionNames)) === false) {
                    continue;
                } else {
                    $actionMetadata = $displayActionMetadatas[$foundActionIndex];
                }

                $specialtyMetadata = $manifest->getMetadata(array(
                    'type'          => 'Tool',
                    'name'          => 'specialtyName',
                    'providerName'  => $providerName,
                    'specialtyName' => '_Global',
                    'clientName'    => 'console'
                    ));

                // lets do the main _Global action first
                $actionableGlobalLongParamMetadata = $manifest->getMetadata(array(
                    'type'          => 'Tool',
                    'name'          => 'actionableMethodLongParams',
                    'providerName'  => $providerName,
                    'specialtyName' => '_Global',
                    'actionName'    => $actionName,
                    'clientName'    => 'console'
                    ));

                $actionableGlobalMetadatas = $manifest->getMetadatas(array(
                    'type'          => 'Tool',
                    'name'          => 'actionableMethodLongParams',
                    'providerName'  => $providerName,
                    'actionName'    => $actionName,
                    'clientName'    => 'console'
                    ));

                if ($actionableGlobalLongParamMetadata) {

                    if (!$providerNameDisplayed) {
                        $this->_respondWithProviderName($providerMetadata);
                        $providerNameDisplayed = true;
                    }

                    $this->_respondWithCommand($providerMetadata, $actionMetadata, $specialtyMetadata, $actionableGlobalLongParamMetadata);

                    $actionIsGlobal = true;
                } else {
                    $actionIsGlobal = false;
                }

                // check for providers without a _Global action
                $isSingleSpecialProviderAction = false;
                if (!$actionIsGlobal && count($actionableGlobalMetadatas) == 1) {
                    $isSingleSpecialProviderAction = true;
                    $this->_respondWithProviderName($providerMetadata);
                    $providerNameDisplayed = true;
                }

                if ($includeAllSpecialties || $isSingleSpecialProviderAction) {

                    foreach ($providerSignature->getSpecialties() as $specialtyName) {

                        if ($specialtyName == '_Global') {
                            continue;
                        }

                        $specialtyMetadata = $manifest->getMetadata(array(
                            'type'          => 'Tool',
                            'name'          => 'specialtyName',
                            'providerName'  => $providerMetadata->getProviderName(),
                            'specialtyName' => $specialtyName,
                            'clientName'    => 'console'
                            ));

                        $actionableSpecialtyLongMetadata = $manifest->getMetadata(array(
                            'type'          => 'Tool',
                            'name'          => 'actionableMethodLongParams',
                            'providerName'  => $providerMetadata->getProviderName(),
                            'specialtyName' => $specialtyName,
                            'actionName'    => $actionName,
                            'clientName'    => 'console'
                            ));

                        if($actionableSpecialtyLongMetadata) {
                            $this->_respondWithCommand($providerMetadata, $actionMetadata, $specialtyMetadata, $actionableSpecialtyLongMetadata);
                        }

                    }
                }

                // reset the special flag for single provider action with specialty
                $isSingleSpecialProviderAction = false;

                if (!$includeAllSpecialties && count($actionableGlobalMetadatas) > 1) {
                    $this->_response->appendContent('    Note: There are specialties, use ', array('color' => 'yellow', 'separator' => false));
                    $this->_response->appendContent(
                        'zf ' . $actionMetadata->getValue() . ' ' . $providerMetadata->getValue() . '.?',
                        array('color' => 'cyan', 'separator' => false)
                        );
                    $this->_response->appendContent(' to get specific help on them.', array('color' => 'yellow'));
                }

            }

            if ($providerNameDisplayed) {
                $this->_response->appendContent(null, array('separator' => true));
            }
        }
        return $this;
    }

    /**
     * _respondWithProviderName()
     *
     * @param Zend_Tool_Framework_Metadata_Tool $providerMetadata
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _respondWithProviderName(Zend_Tool_Framework_Metadata_Tool $providerMetadata)
    {
        $this->_response->appendContent('  ' . $providerMetadata->getProviderName());
        return $this;
    }

    /**
     * _respondWithCommand()
     *
     * @param Zend_Tool_Framework_Metadata_Tool $providerMetadata
     * @param Zend_Tool_Framework_Metadata_Tool $actionMetadata
     * @param Zend_Tool_Framework_Metadata_Tool $specialtyMetadata
     * @param Zend_Tool_Framework_Metadata_Tool $parameterLongMetadata
     * @return Zend_Tool_Framework_Client_Console_HelpSystem
     */
    protected function _respondWithCommand(
        Zend_Tool_Framework_Metadata_Tool $providerMetadata,
        Zend_Tool_Framework_Metadata_Tool $actionMetadata,
        Zend_Tool_Framework_Metadata_Tool $specialtyMetadata,
        Zend_Tool_Framework_Metadata_Tool $parameterLongMetadata)//,
        //Zend_Tool_Framework_Metadata_Tool $parameterShortMetadata)
    {
        $this->_response->appendContent(
            '    zf ' . $actionMetadata->getValue() . ' ' . $providerMetadata->getValue(),
            array('color' => 'cyan', 'separator' => false)
            );

        if ($specialtyMetadata->getSpecialtyName() != '_Global') {
            $this->_response->appendContent('.' . $specialtyMetadata->getValue(), array('color' => 'cyan', 'separator' => false));
        }

        foreach ($parameterLongMetadata->getValue() as $paramName => $consoleParamName) {
            $methodInfo = $parameterLongMetadata->getReference();
            $paramString = ' ' . $consoleParamName;
            if ( ($defaultValue = $methodInfo['parameterInfo'][$paramName]['default']) != null) {
                $paramString .= '[=' . $defaultValue . ']';
            }
            $this->_response->appendContent($paramString . '', array('separator' => false));
        }

       $this->_response->appendContent(null, array('separator' => true));
       return $this;
    }

}
