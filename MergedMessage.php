<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\MessageQueue;

/**
 * Provides mapping between merged message adn appropriate original messages ids.
 */
class MergedMessage implements MergedMessageInterface
{
    /**
     * @var object
     */
    private $mergedMessage;

    /**
     * @var array
     */
    private $originalMessagesIds;

    /**
     * @param $mergedMessage
     * @param array $originalMessagesIds
     */
    public function __construct($mergedMessage, array $originalMessagesIds)
    {
        $this->mergedMessage = $mergedMessage;
        $this->originalMessagesIds = $originalMessagesIds;
    }

    /**
     * @inheritdoc
     */
    public function getMergedMessage()
    {
        return $this->mergedMessage;
    }

    /**
     * @inheritdoc
     */
    public function getOriginalMessagesIds()
    {
        return $this->originalMessagesIds;
    }
}
