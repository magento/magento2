<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\JwtFrameworkAdapter\Model;

use Jose\Component\Core\AlgorithmManager;
use Jose\Component\Signature\JWSBuilder;

class JwsBuilderFactory
{
    /**
     * @var AlgorithmManager
     */
    private $algoManager;

    public function __construct(JwsAlgorithmManagerFactory $algorithmManagerFactory) {
        $this->algoManager = $algorithmManagerFactory->create();
    }

    public function create(): JWSBuilder
    {
        return  new JWSBuilder($this->algoManager);
    }
}
