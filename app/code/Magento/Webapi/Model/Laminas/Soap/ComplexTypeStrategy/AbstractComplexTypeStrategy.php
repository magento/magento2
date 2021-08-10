<?php
/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Api\Data\ComplexTypeStrategyInterface;
use Magento\Webapi\Api\Data\DocumentationStrategyInterface;
use Magento\Webapi\Model\Laminas\Soap\Wsdl;

/**
 * Abstract class for Magento\Webapi\Model\Laminas\Soap\Wsdl\Strategy.
 */
abstract class AbstractComplexTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * Context object.
     *
     * @var Wsdl
     */
    protected $context;

    /**
     * @var DocumentationStrategyInterface
     */
    protected $documentationStrategy;

    /**
     * @inheritdoc
     */
    public function setContext(Wsdl $context)
    {
        $this->context = $context;
    }

    /**
     * Return the current WSDL context object.
     *
     * @return Wsdl
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * Look through registered types.
     *
     * @param string $phpType
     *
     * @return null|string
     */
    public function scanRegisteredTypes(string $phpType)
    {
        if (array_key_exists($phpType, $this->getContext()->getTypes())) {
            $soapTypes = $this->getContext()->getTypes();
            return $soapTypes[$phpType];
        }

        return null;
    }

    /**
     * Sets the strategy for generating complex type documentation.
     *
     * @param DocumentationStrategyInterface $documentationStrategy
     *
     * @return void
     */
    public function setDocumentationStrategy($documentationStrategy)
    {
        $this->documentationStrategy = $documentationStrategy;
    }
}
