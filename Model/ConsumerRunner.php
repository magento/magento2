<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\MessageQueue\Model;

use Magento\Framework\MessageQueue\ConsumerFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Consumer runner class is used to run consumer, which name matches the magic method invoked on this class.
 *
 * Is used to schedule consumers execution in crontab.xml as follows:
 * <code>
 * <job name="consumerConsumerName" instance="Magento\MessageQueue\Model\ConsumerRunner" method="consumerName">
 * </code>
 * Where <i>consumerName</i> should be a valid name of consumer registered in some queue.xml
 */
class ConsumerRunner
{
    /**
     * @var ConsumerFactory
     */
    private $consumerFactory;

    /**
     * Initialize dependencies.
     *
     * @param ConsumerFactory $consumerFactory
     */
    public function __construct(ConsumerFactory $consumerFactory)
    {
        $this->consumerFactory = $consumerFactory;
    }

    /**
     * Process messages in queue using consumer, which name is equal to the current magic method name.
     *
     * @param string $name
     * @param array $arguments
     * @throws LocalizedException
     * @return void
     */
    public function __call($name, $arguments)
    {
        try {
            $consumer = $this->consumerFactory->get($name);
        } catch (\Exception $e) {
            $errorMsg = '"%callbackMethod" callback method specified in crontab.xml '
                . 'must have corresponding consumer declared in some queue.xml.';
            throw new LocalizedException(__($errorMsg, ['callbackMethod' => $name]));
        }
        $consumer->process();
    }
}
