<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Csp\Model\Collector;

use Magento\Csp\Api\PolicyCollectorInterface;
use Magento\Csp\Model\Collector\CspWhitelistXml\Reader as ConfigReader;
use Magento\Csp\Model\Policy\FetchPolicy;

/**
 * Collects policies defined in csp_whitelist.xml configs.
 */
class CspWhitelistXmlCollector implements PolicyCollectorInterface
{
    /**
     * @var ConfigReader
     */
    private $configReader;

    /**
     * @param ConfigReader $configReader
     */
    public function __construct(ConfigReader $configReader)
    {
        $this->configReader = $configReader;
    }

    /**
     * @inheritDoc
     */
    public function collect(array $defaultPolicies = []): array
    {
        $policies = $defaultPolicies;
        $config = $this->configReader->read();
        foreach ($config as $policyId => $values) {
            $policies[] = new FetchPolicy(
                $policyId,
                false,
                $values['hosts'],
                [],
                false,
                false,
                false,
                [],
                $values['hashes'],
                false,
                false
            );
        }

        return $policies;
    }
}
