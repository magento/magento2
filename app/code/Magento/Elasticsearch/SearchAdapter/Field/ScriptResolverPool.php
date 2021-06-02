<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\SearchAdapter\Field;

class ScriptResolverPool implements ScriptResolverPoolInterface
{
    /**
     * @var ScriptResolverInterface[]
     */
    private $scriptResolvers;

    /**
     * @param ScriptResolverInterface[] $scriptResolvers
     * @throws \InvalidArgumentException
     */
    public function __construct(array $scriptResolvers)
    {
        $this->scriptResolvers = $scriptResolvers;

        foreach ($this->scriptResolvers as $scriptResolver) {
            if (!$scriptResolver instanceof ScriptResolverInterface) {
                throw new \InvalidArgumentException(
                    sprintf(
                        '%s doesn\'t implement %s.',
                        get_class($scriptResolver),
                        ScriptResolverInterface::class
                    )
                );
            }
        }
    }

    public function getScriptResolvers()
    {
        return $this->getScriptResolvers();
    }

    public function getFieldScriptResolver(string $fieldName): ?ScriptResolverInterface
    {
        return $this->scriptResolvers[$fieldName] ?? null;
    }
}
