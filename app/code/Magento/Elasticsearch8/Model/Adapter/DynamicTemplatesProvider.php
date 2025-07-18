<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch8\Model\Adapter;

use Magento\Elasticsearch8\Model\Adapter\DynamicTemplates\MapperInterface;
use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Elasticsearch dynamic templates provider.
 * @deprecated Elasticsearch8 is no longer supported by Adobe
 * @see this class will be responsible for ES8 only
 */
class DynamicTemplatesProvider
{
    /**
     * @var array
     */
    private array $mappers;

    /**
     * @param MapperInterface[] $mappers
     */
    public function __construct(array $mappers)
    {
        $this->mappers = $mappers;
    }

    /**
     * Get elasticsearch dynamic templates.
     *
     * @return array
     * @throws InvalidArgumentException
     */
    public function getTemplates(): array
    {
        $templates = [];
        foreach ($this->mappers as $mapper) {
            if (!$mapper instanceof MapperInterface) {
                throw new InvalidArgumentException(
                    __('Mapper %1 should implement %2', get_class($mapper), MapperInterface::class)
                );
            }
            $templates = $mapper->processTemplates($templates);
        }

        return $templates;
    }
}
