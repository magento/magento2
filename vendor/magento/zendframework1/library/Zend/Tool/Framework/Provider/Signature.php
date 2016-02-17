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
 * @see Zend_Reflection_Class
 */
#require_once 'Zend/Reflection/Class.php';

/**
 * @see Zend_Tool_Framework_Registry
 */
#require_once 'Zend/Tool/Framework/Registry/EnabledInterface.php';

/**
 * @see Zend_Tool_Framework_Action_Base
 */
#require_once 'Zend/Tool/Framework/Action/Base.php';

/**
 * The purpose of Zend_Tool_Framework_Provider_Signature is to derive
 * callable signatures from the provided provider.
 *
 * @category   Zend
 * @package    Zend_Tool
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Tool_Framework_Provider_Signature implements Zend_Tool_Framework_Registry_EnabledInterface
{

    /**
     * @var Zend_Tool_Framework_Registry
     */
    protected $_registry = null;

    /**
     * @var Zend_Tool_Framework_Provider_Interface
     */
    protected $_provider = null;

    /**
     * @var string
     */
    protected $_name = null;

    /**
     * @var array
     */
    protected $_specialties = array();

    /**
     * @var array
     */
    protected $_actionableMethods = array();

    /**
     * @var unknown_type
     */
    protected $_actions = array();

    /**
     * @var Zend_Reflection_Class
     */
    protected $_providerReflection = null;

    /**
     * @var bool
     */
    protected $_isProcessed = false;

    /**
     * Constructor
     *
     * @param Zend_Tool_Framework_Provider_Interface $provider
     */
    public function __construct(Zend_Tool_Framework_Provider_Interface $provider)
    {
        $this->_provider = $provider;
        $this->_providerReflection = new Zend_Reflection_Class($provider);
    }

    /**
     * setRegistry()
     *
     * @param Zend_Tool_Framework_Registry_Interface $registry
     * @return Zend_Tool_Framework_Provider_Signature
     */
    public function setRegistry(Zend_Tool_Framework_Registry_Interface $registry)
    {
        $this->_registry = $registry;
        return $this;
    }

    public function process()
    {
        if ($this->_isProcessed) {
            return;
        }

        $this->_process();
    }

    /**
     * getName() of the provider
     *
     * @return unknown
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Get the provider for this signature
     *
     * @return Zend_Tool_Framework_Provider_Interface
     */
    public function getProvider()
    {
        return $this->_provider;
    }

    /**
     * getProviderReflection()
     *
     * @return Zend_Reflection_Class
     */
    public function getProviderReflection()
    {
        return $this->_providerReflection;
    }

    /**
     * getSpecialities()
     *
     * @return array
     */
    public function getSpecialties()
    {
        return $this->_specialties;
    }

    /**
     * getActions()
     *
     * @return array Array of Actions
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * getActionableMethods()
     *
     * @return array
     */
    public function getActionableMethods()
    {
        return $this->_actionableMethods;
    }

    /**
     * getActionableMethod() - Get an actionable method by name, this will return an array of
     * useful information about what can be exectued on this provider
     *
     * @param string $methodName
     * @return array
     */
    public function getActionableMethod($methodName)
    {
        if (isset($this->_actionableMethods[$methodName])) {
            return $this->_actionableMethods[$methodName];
        }

        return false;
    }

    /**
     * getActionableMethodByActionName() - Get an actionable method by its action name, this
     * will return an array of useful information about what can be exectued on this provider
     *
     * @param string $actionName
     * @return array
     */
    public function getActionableMethodByActionName($actionName, $specialtyName = '_Global')
    {
        foreach ($this->_actionableMethods as $actionableMethod) {
            if ($actionName == $actionableMethod['actionName']
                && $specialtyName == $actionableMethod['specialty']) {
                return $actionableMethod;
            }
        }

        return false;
    }

    /**
     * _process() is called at construction time and is what will build the signature information
     * for determining what is actionable
     *
     */
    protected function _process()
    {
        $this->_isProcessed = true;
        $this->_processName();
        $this->_processSpecialties();
        $this->_processActionableMethods();
    }

    /**
     * _processName();
     *
     */
    protected function _processName()
    {
        if (method_exists($this->_provider, 'getName')) {
            $this->_name = $this->_provider->getName();
        }

        if ($this->_name == null) {
            $className = get_class($this->_provider);
            $name = $className;
            if (strpos($name, '_')) {
                $name = substr($name, strrpos($name, '_')+1);
            }
            $name = preg_replace('#(Provider|Manifest)$#', '', $name);
            $this->_name = $name;
        }
    }

    /**
     * _processSpecialties() - Break out the specialty names for this provider
     *
     */
    protected function _processSpecialties()
    {
        $specialties = array();

        if ($this->_providerReflection->hasMethod('getSpecialties')) {
            $specialties = $this->_provider->getSpecialties();
            if (!is_array($specialties)) {
                #require_once 'Zend/Tool/Framework/Provider/Exception.php';
                throw new Zend_Tool_Framework_Provider_Exception(
                    'Provider ' . get_class($this->_provider) . ' must return an array for method getSpecialties().'
                    );
            }
        } else {
            $defaultProperties = $this->_providerReflection->getDefaultProperties();
            $specialties = (isset($defaultProperties['_specialties'])) ? $defaultProperties['_specialties'] : array();
            if (!is_array($specialties)) {
                #require_once 'Zend/Tool/Framework/Provider/Exception.php';
                throw new Zend_Tool_Framework_Provider_Exception(
                    'Provider ' . get_class($this->_provider) . '\'s property $_specialties must be an array.'
                    );
            }
        }

        $this->_specialties = array_merge(array('_Global'), $specialties);

    }

    /**
     * _processActionableMethods() - process all methods that can be called on this provider.
     *
     */
    protected function _processActionableMethods()
    {

        $specialtyRegex = '#(.*)(' . implode('|', $this->_specialties) . ')$#i';


        $methods = $this->_providerReflection->getMethods();

        $actionableMethods = array();
        foreach ($methods as $method) {

            $methodName = $method->getName();

            /**
             * the following will determine what methods are actually actionable
             * public, non-static, non-underscore prefixed, classes that dont
             * contain the name "
             */
            if (!$method->getDeclaringClass()->isInstantiable()
                || !$method->isPublic()
                || $methodName[0] == '_'
                || $method->isStatic()
                || in_array($methodName, array('getContextClasses', 'getName')) // other protected public methods will nee to go here
                ) {
                continue;
            }

            /**
             * check to see if the method was a required method by a Zend_Tool_* interface
             */
            foreach ($method->getDeclaringClass()->getInterfaces() as $methodDeclaringClassInterface) {
                if (strpos($methodDeclaringClassInterface->getName(), 'Zend_Tool_') === 0
                    && $methodDeclaringClassInterface->hasMethod($methodName)) {
                    continue 2;
                }
            }

            $actionableName = ucfirst($methodName);

            if (substr($actionableName, -6) == 'Action') {
                $actionableName = substr($actionableName, 0, -6);
            }

            $actionableMethods[$methodName]['methodName'] = $methodName;

            $matches = null;
            if (preg_match($specialtyRegex, $actionableName, $matches)) {
                $actionableMethods[$methodName]['actionName'] = $matches[1];
                $actionableMethods[$methodName]['specialty'] = $matches[2];
            } else {
                $actionableMethods[$methodName]['actionName'] = $actionableName;
                $actionableMethods[$methodName]['specialty'] = '_Global';
            }

            // get the action, and create non-existent actions when they dont exist (the true part below)
            $action = $this->_registry->getActionRepository()->getAction($actionableMethods[$methodName]['actionName']);
            if ($action == null) {
                $action = new Zend_Tool_Framework_Action_Base($actionableMethods[$methodName]['actionName']);
                $this->_registry->getActionRepository()->addAction($action);
            }
            $actionableMethods[$methodName]['action'] = $action;

            if (!in_array($actionableMethods[$methodName]['action'], $this->_actions)) {
                $this->_actions[] = $actionableMethods[$methodName]['action'];
            }

            $parameterInfo = array();
            $position = 1;
            foreach ($method->getParameters() as $parameter) {
                $currentParam = $parameter->getName();
                $parameterInfo[$currentParam]['position']    = $position++;
                $parameterInfo[$currentParam]['optional']    = $parameter->isOptional();
                $parameterInfo[$currentParam]['default']     = ($parameter->isOptional()) ? $parameter->getDefaultValue() : null;
                $parameterInfo[$currentParam]['name']        = $currentParam;
                $parameterInfo[$currentParam]['type']        = 'string';
                $parameterInfo[$currentParam]['description'] = null;
            }

            $matches = null;
            if (($docComment = $method->getDocComment()) != '' &&
                (preg_match_all('/@param\s+(\w+)+\s+(\$\S+)\s+(.*?)(?=(?:\*\s*@)|(?:\*\/))/s', $docComment, $matches)))
            {
                for ($i=0; $i <= count($matches[0])-1; $i++) {
                    $currentParam = ltrim($matches[2][$i], '$');

                    if ($currentParam != '' && isset($parameterInfo[$currentParam])) {

                        $parameterInfo[$currentParam]['type'] = $matches[1][$i];

                        $descriptionSource = $matches[3][$i];

                        if ($descriptionSource != '') {
                            $parameterInfo[$currentParam]['description'] = trim($descriptionSource);
                        }

                    }

                }

            }

            $actionableMethods[$methodName]['parameterInfo'] = $parameterInfo;

        }

        $this->_actionableMethods = $actionableMethods;
    }

}
