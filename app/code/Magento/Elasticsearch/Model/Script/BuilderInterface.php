<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Model\Script;

interface BuilderInterface
{
    /**
     * @param ScriptInterface $script
     * @param float[] $parameters
     * @return array
     */
    public function buildScript(ScriptInterface $script, array $parameters = []): array;
}
