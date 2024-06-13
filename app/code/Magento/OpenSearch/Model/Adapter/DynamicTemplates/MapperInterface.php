<?php
/**
 * Copyright 2022 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Model\Adapter\DynamicTemplates;

/**
 * Dynamic templates' mapper.
 */
interface MapperInterface
{
    /**
     * Add/remove/edit dynamic template mapping.
     *
     * @param array $templates
     * @return array
     */
    public function processTemplates(array $templates): array;
}
