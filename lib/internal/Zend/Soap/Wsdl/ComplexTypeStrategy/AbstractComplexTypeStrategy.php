<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Soap
 */

namespace Zend\Soap\Wsdl\ComplexTypeStrategy;

/**
 * Abstract class for Zend_Soap_Wsdl_Strategy.
 *
 * @category   Zend
 * @package    Zend_Soap
 * @subpackage WSDL
 */
abstract class AbstractComplexTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * Context object
     *
     * @var \Zend\Soap\Wsdl
     */
    protected $context;

    /**
     * Set the Zend_Soap_Wsdl Context object this strategy resides in.
     *
     * @param \Zend\Soap\Wsdl $context
     * @return void
     */
    public function setContext(\Zend\Soap\Wsdl $context)
    {
        $this->context = $context;
    }

    /**
     * Return the current Zend_Soap_Wsdl context object
     *
     * @return \Zend\Soap\Wsdl
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Look through registered types
     *
     * @param string $phpType
     * @return string
     */
    public function scanRegisteredTypes($phpType)
    {
        if (array_key_exists($phpType, $this->getContext()->getTypes())) {
            $soapTypes = $this->getContext()->getTypes();
            return $soapTypes[$phpType];
        }

        return null;
    }
}
