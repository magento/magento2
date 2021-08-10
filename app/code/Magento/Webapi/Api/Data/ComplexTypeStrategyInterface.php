<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Api\Data;

use Magento\Webapi\Model\Laminas\Soap\Wsdl;

/**
 * Interface strategies that generate an XSD-Schema for complex data types in WSDL files.
 */
interface ComplexTypeStrategyInterface
{
    /**
     * Method accepts the current WSDL context file.
     *
     * @param Wsdl $context
     */
    public function setContext(Wsdl $context);

    /**
     * Create a complex type based on a strategy
     *
     * @param string $type
     * @return string XSD type
     */
    public function addComplexType(string $type);
}
