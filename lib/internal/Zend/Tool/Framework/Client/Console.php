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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Console.php 23204 2010-10-21 15:35:21Z ralph $
 */

/**
 * @see Zend_Tool_Framework_Client_Abstract
 */
#require_once 'Zend/Tool/Framework/Client/Abstract.php';

/**
 * @see Zend_Tool_Framework_Client_Interactive_InputInterface
 */
#require_once 'Zend/Tool/Framework/Client/Interactive/InputInterface.php';

/**
 * @see Zend_Tool_Framework_Client_Interactive_OutputInterface
 */
#require_once 'Zend/Tool/Framework/Client/Interactive/OutputInterface.php';

/**
 * Zend_Tool_Framework_Client_Console - the CLI Client implementation for Zend_Tool_Framework
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Client_Console
    extends Zend_Tool_Framework_Client_Abstract
    implements Zend_Tool_Framework_Client_Interactive_InputInterface,
               Zend_Tool_Framework_Client_Interactive_OutputInterface
{

    /**
     * @var array
     */
    protected $_configOptions = null;

    /**
     * @var array
     */
    protected $_storageOptions = null;

    /**
     * @var Zend_Filter_Word_CamelCaseToDash
     */
    protected $_filterToClientNaming = null;

    /**
     * @var Zend_Filter_Word_DashToCamelCase
     */
    protected $_filterFromClientNaming = null;

    /**
     * @var array
     */
    protected $_classesToLoad = array();
    
    /**
     * main() - This is typically called from zf.php. This method is a
     * self contained main() function.
     *
     */
    public static function main($options = array())
    {
        $cliClient = new self($options);
        $cliClient->dispatch();
    }

    /**
     * getName() - return the name of the client, in this case 'console'
     *
     * @return string
     */
    public function getName()
    {
        return 'console';
    }
    
    /**
     * setConfigOptions()
     * 
     * @param $configOptions
     */
    public function setConfigOptions($configOptions)
    {
        $this->_configOptions = $configOptions;
        return $this;
    }

    /**
     * setStorageOptions()
     * 
     * @param $storageOptions
     */
    public function setStorageOptions($storageOptions)
    {
        $this->_storageOptions = $storageOptions;
        return $this;
    }
    
    public function setClassesToLoad($classesToLoad)
    {
        $this->_classesToLoad = $classesToLoad;
        return $this;
    }

    /**
     * _init() - Tasks processed before the constructor, generally setting up objects to use
     *
     */
    protected function _preInit()
    {
        $config = $this->_registry->getConfig();

        if ($this->_configOptions != null) {
            $config->setOptions($this->_configOptions);
        }

        $storage = $this->_registry->getStorage();

        if ($this->_storageOptions != null && isset($this->_storageOptions['directory'])) {
            $storage->setAdapter(
                new Zend_Tool_Framework_Client_Storage_Directory($this->_storageOptions['directory'])
                );
        }

        // which classes are essential to initializing Zend_Tool_Framework_Client_Console
        $classesToLoad = array(
            'Zend_Tool_Framework_Client_Console_Manifest',    
            'Zend_Tool_Framework_System_Manifest'
            );
            
        if ($this->_classesToLoad) {
            if (is_string($this->_classesToLoad)) {
                $classesToLoad[] = $this->_classesToLoad;
            } elseif (is_array($this->_classesToLoad)) {
                $classesToLoad = array_merge($classesToLoad, $this->_classesToLoad);
            }
        }
        
        // add classes to the basic loader from the config file basicloader.classes.1 ..
        if (isset($config->basicloader) && isset($config->basicloader->classes)) {
            foreach ($config->basicloader->classes as $classKey => $className) {
                array_push($classesToLoad, $className);
            }
        }

        $this->_registry->setLoader(
            new Zend_Tool_Framework_Loader_BasicLoader(array('classesToLoad' => $classesToLoad))
            );

        return;
    }

    /**
     * _preDispatch() - Tasks handed after initialization but before dispatching
     *
     */
    protected function _preDispatch()
    {
        $response = $this->_registry->getResponse();

        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_AlignCenter());
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Indention());
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Blockize());

        if (function_exists('posix_isatty')) {
            $response->addContentDecorator(new Zend_Tool_Framework_Client_Console_ResponseDecorator_Colorizer());
        }
        
        $response->addContentDecorator(new Zend_Tool_Framework_Client_Response_ContentDecorator_Separator())
            ->setDefaultDecoratorOptions(array('separator' => true));

        $optParser = new Zend_Tool_Framework_Client_Console_ArgumentParser();
        $optParser->setArguments($_SERVER['argv'])
            ->setRegistry($this->_registry)
            ->parse();

        return;
    }

    /**
     * _postDispatch() - Tasks handled after dispatching
     *
     */
    protected function _postDispatch()
    {
        $request = $this->_registry->getRequest();
        $response = $this->_registry->getResponse();

        if ($response->isException()) {
            $helpSystem = new Zend_Tool_Framework_Client_Console_HelpSystem();
            $helpSystem->setRegistry($this->_registry)
                ->respondWithErrorMessage($response->getException()->getMessage(), $response->getException())
                ->respondWithSpecialtyAndParamHelp(
                    $request->getProviderName(),
                    $request->getActionName()
                    );
        }

        echo PHP_EOL;
        return;
    }

    /**
     * handleInteractiveInputRequest() is required by the Interactive InputInterface
     *
     *
     * @param Zend_Tool_Framework_Client_Interactive_InputRequest $inputRequest
     * @return string
     */
    public function handleInteractiveInputRequest(Zend_Tool_Framework_Client_Interactive_InputRequest $inputRequest)
    {
        fwrite(STDOUT, $inputRequest->getContent() . PHP_EOL . 'zf> ');
        $inputContent = fgets(STDIN);
        return rtrim($inputContent); // remove the return from the end of the string
    }

    /**
     * handleInteractiveOutput() is required by the Interactive OutputInterface
     *
     * This allows us to display output immediately from providers, rather
     * than displaying it after the provider is done.
     *
     * @param string $output
     */
    public function handleInteractiveOutput($output)
    {
        echo $output;
    }

    /**
     * getMissingParameterPromptString()
     *
     * @param Zend_Tool_Framework_Provider_Interface $provider
     * @param Zend_Tool_Framework_Action_Interface $actionInterface
     * @param string $missingParameterName
     * @return string
     */
    public function getMissingParameterPromptString(Zend_Tool_Framework_Provider_Interface $provider, Zend_Tool_Framework_Action_Interface $actionInterface, $missingParameterName)
    {
        return 'Please provide a value for $' . $missingParameterName;
    }


    /**
     * convertToClientNaming()
     *
     * Convert words to client specific naming, in this case is lower, dash separated
     *
     * Filters are lazy-loaded.
     *
     * @param string $string
     * @return string
     */
    public function convertToClientNaming($string)
    {
        if (!$this->_filterToClientNaming) {
            $filter = new Zend_Filter();
            $filter->addFilter(new Zend_Filter_Word_CamelCaseToDash());
            $filter->addFilter(new Zend_Filter_StringToLower());

            $this->_filterToClientNaming = $filter;
        }

        return $this->_filterToClientNaming->filter($string);
    }

    /**
     * convertFromClientNaming()
     *
     * Convert words from client specific naming to code naming - camelcased
     *
     * Filters are lazy-loaded.
     *
     * @param string $string
     * @return string
     */
    public function convertFromClientNaming($string)
    {
        if (!$this->_filterFromClientNaming) {
            $this->_filterFromClientNaming = new Zend_Filter_Word_DashToCamelCase();
        }

        return $this->_filterFromClientNaming->filter($string);
    }

}
