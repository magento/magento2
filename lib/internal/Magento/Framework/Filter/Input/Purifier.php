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
     * @var HTMLPurifier|null
     */
    private $purifier;

    /**
     * Purifier Constructor Call
     * @param HTMLPurifier|null $purifier
     */
    public function __construct(
        ?HTMLPurifier $purifier = null
    ) {
        $config = HTMLPurifier_Config::createDefault();
        $config->set(self::CACHE_DEFINITION, null);

        $this->purifier = $purifier ?? ObjectManager::getInstance()->create(HTMLPurifier::class, ['config' => $config]);
    }

    /**
     * Purify Html Content from malicious code
     *
     * @param string|array $content
     * @return mixed
     */
    public function purify($content) :mixed
    {
        return is_array($content) ? $this->purifier->purifyArray($content) : $this->purifier->purify($content);
    }
}
