<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Dto\Config;

use Magento\Framework\Config\Dom\UrnResolver;
use Magento\Framework\Config\SchemaLocatorInterface;
use Magento\Framework\Exception\NotFoundException;

class SchemaLocator implements SchemaLocatorInterface
{
    /**
     * @var UrnResolver
     */
    protected $urnResolver;

    /**
     * @param UrnResolver $urnResolver
     */
    public function __construct(UrnResolver $urnResolver)
    {
        $this->urnResolver = $urnResolver;
    }

    /**
     * Get path to merged config schema
     *
     * @return string
     * @throws NotFoundException
     */
    public function getSchema()
    {
        return $this->urnResolver->getRealPath('urn:magento:framework:Dto/etc/dto.xsd');
    }

    /**
     * Get path to pre file validation schema
     *
     * @return string
     * @throws NotFoundException
     */
    public function getPerFileSchema()
    {
        return $this->getSchema();
    }
}
