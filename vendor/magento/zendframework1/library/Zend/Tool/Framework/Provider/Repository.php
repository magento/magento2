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
 * @see Zend_Tool_Framework_Provider_Signature
 */
#require_once 'Zend/Tool/Framework/Provider/Signature.php';

/**
 * @see Zend_Tool_Framework_Registry_EnabledInterface
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

/**
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Provider_Repository
    implements Zend_Tool_Framework_Registry_EnabledInterface, IteratorAggregate, Countable
{

    /**
     * @var Zend_Tool_Framework_Registry
     */
    protected $_registry = null;

    /**
     * @var bool
     */
    protected $_processOnAdd = false;

    /**
     * @var Zend_Tool_Framework_Provider_Interface[]
     */
    protected $_unprocessedProviders = array();

    /**
     * @var Zend_Tool_Framework_Provider_Signature[]
     */
    protected $_providerSignatures = array();

    /**
     * @var array Array of Zend_Tool_Framework_Provider_Inteface
     */
    protected $_providers = array();

    /**
     * setRegistry()
     *
     * @param Zend_Tool_Framework_Registry_Interface $registry
     * @return unknown
     */
    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    /**
     * Set the ProcessOnAdd flag
     *
     * @param unknown_type $processOnAdd
     * @return unknown
     */
    public function setProcessOnAdd($processOnAdd = true)
    {
        $this->_processOnAdd = (bool) $processOnAdd;
        return $this;
    }

    /**
     * Add a provider to the repository for processing
     *
     * @param Zend_Tool_Framework_Provider_Interface $provider
     * @return Zend_Tool_Framework_Provider_Repository
     */
    public function addProvider(Zend_Tool_Framework_Provider_Interface $provider, $overwriteExistingProvider = false)
    {
        if ($provider instanceof Zend_Tool_Framework_Registry_EnabledInterface) {
            $provider->setRegistry($this->_registry);
        }

        if (method_exists($provider, 'getName')) {
            $providerName = $provider->getName();
        } else {
            $providerName = $this->_parseName($provider);
        }

        // if a provider by the given name already exist, and its not set as overwritable, throw exception
        if (!$overwriteExistingProvider &&
            (array_key_exists($providerName, $this->_unprocessedProviders)
                || array_key_exists($providerName, $this->_providers)))
        {
            #require_once 'Zend/Tool/Framework/Provider/Exception.php';
            throw new Zend_Tool_Framework_Provider_Exception('A provider by the name ' . $providerName
                . ' is already registered and $overrideExistingProvider is set to false.');
        }

        $this->_unprocessedProviders[$providerName] = $provider;

        // if process has already been called, process immediately.
        if ($this->_processOnAdd) {
            $this->process();
        }

        return $this;
    }

    public function hasProvider($providerOrClassName, $processedOnly = true)
    {
        if ($providerOrClassName instanceof Zend_Tool_Framework_Provider_Interface) {
            $targetProviderClassName = get_class($providerOrClassName);
        } else {
            $targetProviderClassName = (string) $providerOrClassName;
        }

        if (!$processedOnly) {
            foreach ($this->_unprocessedProviders as $unprocessedProvider) {
                if (get_class($unprocessedProvider) == $targetProviderClassName) {
                    return true;
                }
            }
        }

        foreach ($this->_providers as $processedProvider) {
            if (get_class($processedProvider) == $targetProviderClassName) {
                return true;
            }
        }

        return false;
    }

    /**
     * Process all of the unprocessed providers
     *
     */
    public function process()
    {

        // process all providers in the unprocessedProviders array
        //foreach ($this->_unprocessedProviders as $providerName => $provider) {
        reset($this->_unprocessedProviders);
        while ($this->_unprocessedProviders) {

            $providerName = key($this->_unprocessedProviders);
            $provider = array_shift($this->_unprocessedProviders);

            // create a signature for the provided provider
            $providerSignature = new Zend_Tool_Framework_Provider_Signature($provider);

            if ($providerSignature instanceof Zend_Tool_Framework_Registry_EnabledInterface) {
                $providerSignature->setRegistry($this->_registry);
            }

            $providerSignature->process();

            // ensure the name is lowercased for easier searching
            $providerName = strtolower($providerName);

            // add to the appropraite place
            $this->_providerSignatures[$providerName] = $providerSignature;
            $this->_providers[$providerName]          = $providerSignature->getProvider();

            if ($provider instanceof Zend_Tool_Framework_Provider_Initializable) {
                $provider->initialize();
            }

        }

    }

    /**
     * getProviders() Get all the providers in the repository
     *
     * @return array
     */
    public function getProviders()
    {
        return $this->_providers;
    }

    /**
     * getProviderSignatures() Get all the provider signatures
     *
     * @return array
     */
    public function getProviderSignatures()
    {
        return $this->_providerSignatures;
    }

    /**
     * getProvider()
     *
     * @param string $providerName
     * @return Zend_Tool_Framework_Provider_Interface
     */
    public function getProvider($providerName)
    {
        return $this->_providers[strtolower($providerName)];
    }

    /**
     * getProviderSignature()
     *
     * @param string $providerName
     * @return Zend_Tool_Framework_Provider_Signature
     */
    public function getProviderSignature($providerName)
    {
        return $this->_providerSignatures[strtolower($providerName)];
    }

    /**
     * count() - return the number of providers
     *
     * @return int
     */
    public function count()
    {
        return count($this->_providers);
    }

    /**
     * getIterator() - Required by the IteratorAggregate Interface
     *
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return new ArrayIterator($this->getProviders());
    }

    /**
     * _parseName - internal method to determine the name of an action when one is not explicity provided.
     *
     * @param Zend_Tool_Framework_Action_Interface $action
     * @return string
     */
    protected function _parseName(Zend_Tool_Framework_Provider_Interface $provider)
    {
        $className = get_class($provider);
        $providerName = $className;
        if (strpos($providerName, '_') !== false) {
            $providerName = substr($providerName, strrpos($providerName, '_')+1);
        }
        if (substr($providerName, -8) == 'Provider') {
            $providerName = substr($providerName, 0, strlen($providerName)-8);
        }
        return $providerName;
    }

}
