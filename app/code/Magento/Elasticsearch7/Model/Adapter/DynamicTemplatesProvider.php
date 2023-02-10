<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch7\Model\Adapter;

use Magento\Elasticsearch7\Model\Adapter\DynamicTemplates\MapperInterface;
use Magento\Framework\Exception\InvalidArgumentException;

/**
 * Elasticsearch dynamic templates provider.
 */
class DynamicTemplatesProvider
{
    /**
     * @var array
     */
    private $mappers;

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
