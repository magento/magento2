<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Filter\Input;

use HTMLPurifier;
use HTMLPurifier_Config;
use Magento\Framework\App\ObjectManager;

class Purifier implements PurifierInterface
{
    public const CACHE_DEFINITION = 'Cache.DefinitionImpl';

    /**
     * @var HTMLPurifier $purifier
     */
    private HTMLPurifier $purifier;

    /**
     * Purifier Constructor Call
     */
    public function __construct()
    {
        $config = HTMLPurifier_Config::createDefault();
        $config->set(self::CACHE_DEFINITION, null);

        $this->purifier = ObjectManager::getInstance()->create(HTMLPurifier::class, ['config' => $config]);
    }

    /**
     * Purify Html Content from malicious code
     *
     * @param string|array $content
     * @return string|array
     */
    public function purify($content)
    {
        return is_array($content) ? $this->purifier->purifyArray($content) : $this->purifier->purify($content);
    }
}
