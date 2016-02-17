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
 * @see Zend_Tool_Framework_Manifest_MetadataManifestable
 */
#require_once 'Zend/Tool/Framework/Manifest/MetadataManifestable.php';

/**
 * @see Zend_Filter
 */
#require_once 'Zend/Filter.php';

/**
 * @see Zend_Filter_Word_CamelCaseToDash
 */
#require_once 'Zend/Filter/Word/CamelCaseToDash.php';

/**
 * @see Zend_Filter_StringToLower
 */
#require_once 'Zend/Filter/StringToLower.php';

/**
 * @see Zend_Tool_Framework_Metadata_Tool
 */
#require_once 'Zend/Tool/Framework/Metadata/Tool.php';

/**
 * @see Zend_Tool_Framework_Registry_EnabledInterface
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

/**
 * Zend_Tool_Framework_Client_ConsoleClient_Manifest
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Manifest
    implements Zend_Tool_Framework_Registry_EnabledInterface,
               Zend_Tool_Framework_Manifest_MetadataManifestable
{

    /**
     * @var Zend_Tool_Framework_Registry_Interface
     */
    protected $_registry = null;

    /**
     * setRegistry() - Required for the Zend_Tool_Framework_Registry_EnabledInterface interface
     *
     * @param Zend_Tool_Framework_Registry_Interface $registry
     * @return Zend_Tool_Framework_Client_Console_Manifest
     */
    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * getMetadata() is required by the Manifest Interface.
     *
     * These are the following metadatas that will be setup:
     *
     * normalizedActionName
     *   - metadata for actions
     *   - value will be a dashed name for the action named in 'actionName'
     * normalizedProviderName
     *   - metadata for providers
     *   - value will be a dashed-name for the provider named in 'providerName'
     * normalizedProviderSpecialtyNames
     *   - metadata for providers
     * normalizedActionableMethodLongParameters
     *   - metadata for providers
     * normalizedActionableMethodShortParameters
     *   - metadata for providers
     *
     * @return array Array of Metadatas
     */
    public function getMetadata()
    {
        $metadatas = array();

        // setup the camelCase to dashed filter to use since cli expects dashed named
        $lowerFilter = new Zend_Filter();
        $lowerFilter->addFilter(new Zend_Filter_StringToLower());

        // get the registry to get the action and provider repository
        $actionRepository   = $this->_registry->getActionRepository();
        $providerRepository = $this->_registry->getProviderRepository();

        // loop through all actions and create a metadata for each
        foreach ($actionRepository->getActions() as $action) {
            // each action metadata will be called
            $metadatas[] = new Zend_Tool_Framework_Metadata_Tool(array(
                'name'            => 'normalizedActionName',
                'value'           => $lowerFilter->filter($action->getName()),
                'reference'       => $action,
                'actionName'      => $action->getName(),
                'clientName'      => 'all'
                ));
        }

        foreach ($providerRepository->getProviderSignatures() as $providerSignature) {

            // create the metadata for the provider's cliProviderName
            $metadatas[] = new Zend_Tool_Framework_Metadata_Tool(array(
                'name'            => 'normalizedProviderName',
                'value'           => $lowerFilter->filter($providerSignature->getName()),
                'reference'       => $providerSignature,
                'clientName'      => 'all',
                'providerName'    => $providerSignature->getName()
                ));

            // create the metadatas for the per provider specialites in providerSpecaltyNames
            foreach ($providerSignature->getSpecialties() as $specialty) {

                if ($specialty == '_Global') {
                    continue;
                }

                $metadatas[] = new Zend_Tool_Framework_Metadata_Tool(array(
                    'name'            => 'normalizedSpecialtyName',
                    'value'           => $lowerFilter->filter($specialty),
                    'reference'       => $providerSignature,
                    'clientName'      => 'all',
                    'providerName'    => $providerSignature->getName(),
                    'specialtyName'   => $specialty
                    ));

            }

            // $actionableMethod is keyed by the methodName (but not used)
            foreach ($providerSignature->getActionableMethods() as $actionableMethodData) {

                $methodLongParams  = array();
                $methodShortParams = array();

                // $actionableMethodData get both the long and short names
                foreach ($actionableMethodData['parameterInfo'] as $parameterInfoData) {

                    // filter to dashed
                    $methodLongParams[$parameterInfoData['name']] = $lowerFilter->filter($parameterInfoData['name']);

                    // simply lower the character, (its only 1 char after all)
                    $methodShortParams[$parameterInfoData['name']] = strtolower($parameterInfoData['name'][0]);

                }

                // create metadata for the long name cliActionableMethodLongParameters
                $metadatas[] = new Zend_Tool_Framework_Metadata_Tool(array(
                    'name'            => 'normalizedActionableMethodLongParams',
                    'value'           => $methodLongParams,
                    'clientName'      => 'console',
                    'providerName'    => $providerSignature->getName(),
                    'specialtyName'   => $actionableMethodData['specialty'],
                    'actionName'      => $actionableMethodData['actionName'],
                    'reference'       => &$actionableMethodData
                    ));

                // create metadata for the short name cliActionableMethodShortParameters
                $metadatas[] = new Zend_Tool_Framework_Metadata_Tool(array(
                    'name'            => 'normalizedActionableMethodShortParams',
                    'value'           => $methodShortParams,
                    'clientName'      => 'console',
                    'providerName'    => $providerSignature->getName(),
                    'specialtyName'   => $actionableMethodData['specialty'],
                    'actionName'      => $actionableMethodData['actionName'],
                    'reference'       => &$actionableMethodData
                    ));

            }

        }

        return $metadatas;
    }

    public function getIndex()
    {
        return 100000;
    }

}
