<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Script;

class Builder implements BuilderInterface
{
    public function buildScript(ScriptInterface $script, array $parameters = []): array
    {
        return array_filter(
            [
                'lang' => $script->getLang(),
                'params' => $parameters,
                // "source" is the canonical keyword since ES 5.6.0,
                // but "inline" is supported by all versions since (at least) 5.0.0.
                'inline' => $script->getSource(),
            ]
        );
    }
}
