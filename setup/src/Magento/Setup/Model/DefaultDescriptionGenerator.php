<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Default description generator for product
 */
class DefaultDescriptionGenerator implements DescriptionGeneratorInterface
{
    /**
     * @var string
     */
    private $defaultDescription;

    /**
     * @param string $defaultDescription
     */
    public function __construct($defaultDescription)
    {
        $this->defaultDescription = $defaultDescription;
    }

    /**
     * @param int $entityIndex
     * @return string
     */
    public function generate($entityIndex)
    {
        return sprintf($this->defaultDescription, $entityIndex);
    }
}
