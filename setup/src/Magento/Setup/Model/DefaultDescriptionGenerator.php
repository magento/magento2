<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model;

/**
 * Default description generator for product
 * @since 2.2.0
 */
class DefaultDescriptionGenerator implements DescriptionGeneratorInterface
{
    /**
     * @var string
     * @since 2.2.0
     */
    private $defaultDescription;

    /**
     * @param string $defaultDescription
     * @since 2.2.0
     */
    public function __construct($defaultDescription)
    {
        $this->defaultDescription = $defaultDescription;
    }

    /**
     * @param int $entityIndex
     * @return string
     * @since 2.2.0
     */
    public function generate($entityIndex)
    {
        return sprintf($this->defaultDescription, $entityIndex);
    }
}
