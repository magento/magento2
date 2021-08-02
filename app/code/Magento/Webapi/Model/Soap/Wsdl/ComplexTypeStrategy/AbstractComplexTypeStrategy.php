<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Webapi\Model\Soap\Wsdl\ComplexTypeStrategy;

use Magento\Webapi\Model\Soap\Wsdl;
use Magento\Webapi\Api\Data\ComplexTypeStrategy\DocumentationStrategyInterface;
use Magento\Webapi\Api\Data\ComplexTypeStrategyInterface;

/**
 * Class AbstractComplexTypeStrategy
 */
abstract class AbstractComplexTypeStrategy implements ComplexTypeStrategyInterface
{
    /**
     * Context object
     *
     * @var Wsdl
     */
    protected $context;

    /**
     * @var DocumentationStrategyInterface
     */
    protected $documentationStrategy;

    /**
     * @inheritDoc
     */
    public function setContext(Wsdl $context): void
    {
        $this->context = $context;
    }

    /**
     * Return the current WSDL context object.
     *
     * @return Wsdl
     */
    public function getContext(): Wsdl
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
    public function scanRegisteredTypes(string $phpType): ?string
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
    public function setDocumentationStrategy(DocumentationStrategyInterface $documentationStrategy): void
    {
        $this->documentationStrategy = $documentationStrategy;
    }
}
