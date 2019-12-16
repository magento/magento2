<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\TestModuleCspUtil\Controller\Csp;

use Magento\Csp\Api\CspAwareActionInterface;
use Magento\Csp\Model\Policy\FetchPolicy;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * CSP Aware controller.
 */
class Aware extends Action implements CspAwareActionInterface
{
    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
    }

    /**
     * @inheritDoc
     */
    public function modifyCsp(array $appliedPolicies): array
    {
        $policies = [];
        foreach ($appliedPolicies as $policy) {
            if ($policy instanceof FetchPolicy
                && in_array('http://controller.magento.com', $policy->getHostSources(), true)
            ) {
                $policies[] = new FetchPolicy(
                    'script-src',
                    false,
                    ['https://controller.magento.com'],
                    [],
                    true,
                    false,
                    false,
                    [],
                    ['H4RRnauTM2X2Xg/z9zkno1crqhsaY3uKKu97uwmnXXE=' => 'sha256']
                );
            }
        }

        return $policies;
    }
}
