<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\System\Observer;

use Magento\Mtf\System\Event\Event;

/**
 * Observer for obtaining response of web api handler.
 */
class WebapiResponse extends AbstractObserver
{
    /**
     * File name of response source.
     */
    const FILE_NAME = 'webapi_response.log';

    /**
     * Collect response source artifact to storage.
     *
     * @param Event $event
     * @return void
     */
    public function process(Event $event)
    {
        $directory = $this->createDestinationDirectory('webapi-response');
        $this->logger->log(json_encode($event->getSubjects()[0]), $directory . '/' . $event->getIdentifier() . '.json');
    }
}
