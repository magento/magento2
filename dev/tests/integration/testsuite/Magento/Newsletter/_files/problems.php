<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

require __DIR__ . '/queue.php';

$problem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
    ->create('Magento\Newsletter\Model\Problem');
// $firstSubscriberId comes from subscribers.php
$problem->setSubscriberId($firstSubscriberId)
    ->setQueueId($queue->getQueueId())
    ->setProblemErrorCode(11)
    ->setProblemErrorText('error text')
    ->save();
