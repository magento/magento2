<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AdvancedSearch\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Search\EngineResolverInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use OpenSearch\Client;

class Data extends AbstractHelper
{

    public const OPENSEARCH = 'opensearch';
    public const MAJOR_VERSION = '2';

    /**
     * @var EngineResolverInterface
     */
    public $engineResolver;

    /**
     * @param Context $context
     * @param EngineResolverInterface $engineResolver
     */
    public function __construct(
        Context $context,
        EngineResolverInterface $engineResolver
    ) {
        parent::__construct($context);
        $this->engineResolver = $engineResolver;
    }

    /**
     * Check if opensearch v2.x
     *
     * @return bool
     */
    public function isClientOpenSearchV2(): bool
    {
        $searchEngine =  $this->engineResolver->getCurrentSearchEngine();
        if (stripos($searchEngine, self::OPENSEARCH) !== false) {
            if (substr(Client::VERSION, 0, 1) == self::MAJOR_VERSION) {
                return true;
            }
        }
        return false;
    }
}
