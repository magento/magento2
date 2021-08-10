<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Api\Data\ComplexTypeStrategyInterface;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;

class AnyType implements ComplexTypeStrategyInterface
{
    /**
     * Not needed in this strategy.
     *
     * @param Wsdl $context
     */
    public function setContext(Wsdl $context)
    {
    }

    /**
     * Returns xsd:anyType regardless of the input.
     *
     * @param  string $type
     * @return string
     */
    public function addComplexType($type)
    {
        return Wsdl::XSD_NS . ':anyType';
    }
}
