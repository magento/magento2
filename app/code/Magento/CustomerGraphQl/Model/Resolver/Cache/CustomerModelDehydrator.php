<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CustomerGraphQl\Model\Resolver\Cache;

use Magento\GraphQlCache\Model\Cache\Query\Resolver\Result\DehydratorInterface;

/**
 * Customer resolver data dehydrator to create snapshot data necessary to restore model.
 */
class CustomerModelDehydrator implements DehydratorInterface
{
    /**
     * @inheritdoc
     */
    public function dehydrate(array &$resolvedValue): void
    {
        if (isset($resolvedValue['model'])) {
            $resolvedValue['model_id'] = $resolvedValue['model']->getId();
            $resolvedValue['model_group_id'] = $resolvedValue['model']->getGroupId();
        }
    }
}
