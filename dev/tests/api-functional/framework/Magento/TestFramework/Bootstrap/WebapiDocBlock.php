<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\TestFramework\Bootstrap;

use Magento\TestFramework\Annotation\ApiConfigFixture;
use Magento\TestFramework\Annotation\ConfigFixture;
use Magento\TestFramework\Event\Transaction;

/**
 * @inheritdoc
 */
class WebapiDocBlock extends \Magento\TestFramework\Bootstrap\DocBlock
{
    /**
     * Get list of subscribers.
     *
     * In addition, register magentoApiDataFixture and magentoConfigFixture
     * annotation processors
     *
     * @param \Magento\TestFramework\Application $application
     * @return array
     */
    protected function _getSubscribers(\Magento\TestFramework\Application $application)
    {
        $subscribers = parent::_getSubscribers($application);
        foreach ($subscribers as $key => $subscriber) {
            if (get_class($subscriber) === ConfigFixture::class || get_class($subscriber) === Transaction::class) {
                unset($subscribers[$key]);
            }
        }
        $subscribers[] = new \Magento\TestFramework\Event\Transaction(
            new \Magento\TestFramework\EventManager(
                [
                    new \Magento\TestFramework\Annotation\DbIsolation(),
                    new \Magento\TestFramework\Annotation\ApiDataFixture(),
                ]
            )
        );
        $subscribers[] = new ApiConfigFixture();

        return $subscribers;
    }
}
