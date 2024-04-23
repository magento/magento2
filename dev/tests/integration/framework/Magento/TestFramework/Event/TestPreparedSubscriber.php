<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Listener of PHPUnit built-in events
 */
namespace Magento\TestFramework\Event;

use PHPUnit\Event\Test\Prepared;
use PHPUnit\Event\Test\PreparedSubscriber;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\Workaround\Override\Config;

final class TestPreparedSubscriber implements PreparedSubscriber
{
    public function notify(\PHPUnit\Event\Test\Prepared $event): void
    {
        $className = $event->test()->className();
        $methodName = $event->test()->methodName();

        $objectManager = Bootstrap::getObjectManager();
        $assetRepo = $objectManager->create($className, ['name' => $methodName]);

        $testData = $event->test()->testData();
        if($testData->hasDataFromDataProvider()){
            $dataSetName = $testData->dataFromDataProvider()->dataSetName();
            $assetRepo->setData($dataSetName, ['']);
        }

        $skipConfig = Config::getInstance()->getSkipConfiguration($assetRepo);
        if ($skipConfig['skip']) {
            $assetRepo->markTestSkipped($skipConfig['skipMessage']);
        }
    }
}
