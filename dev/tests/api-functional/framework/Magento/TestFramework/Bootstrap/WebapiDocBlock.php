<?php
/**
 * Bootstrap of the custom Web API DocBlock annotations.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\TestFramework\Bootstrap;

use Magento\TestFramework\Annotation\ApiConfigFixture;
use Magento\TestFramework\Annotation\ConfigFixture;

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
            if (get_class($subscriber) == ConfigFixture::class) {
                unset($subscribers[$key]);
            }
        }
        $subscribers[] = new \Magento\TestFramework\Annotation\ApiDataFixture($this->_fixturesBaseDir);
        $subscribers[] = new ApiConfigFixture();

        return $subscribers;
    }
}
