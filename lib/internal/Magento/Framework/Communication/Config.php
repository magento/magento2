<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Communication\Config\Data as ConfigData;
use Magento\Framework\Phrase;

/**
 * Class for accessing to communication configuration.
 * @since 2.1.0
 */
class Config implements ConfigInterface
{
    /**
     * @var ConfigData
     * @since 2.1.0
     */
    protected $data;

    /**
     * Initialize dependencies.
     *
     * @param ConfigData $configData
     * @since 2.1.0
     */
    public function __construct(ConfigData $configData)
    {
        $this->data = $configData;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getTopic($topicName)
    {
        $data = $this->data->get(self::TOPICS . '/' . $topicName);
        if ($data === null) {
            throw new LocalizedException(
                new Phrase('Topic "%topic" is not configured.', ['topic' => $topicName])
            );
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getTopicHandlers($topicName)
    {
        $topicData = $this->getTopic($topicName);
        return $topicData[self::TOPIC_HANDLERS];
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function getTopics()
    {
        return $this->data->get(self::TOPICS) ?: [];
    }
}
