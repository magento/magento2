<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Communication;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Communication\Config\Data as ConfigData;
use Magento\Framework\Phrase;

/**
 * Class for accessing to communication configuration.
 */
class Config implements ConfigInterface
{
    /**
     * @var ConfigData
     */
    protected $data;

    /**
     * Initialize dependencies.
     *
     * @param ConfigData $configData
     */
    public function __construct(ConfigData $configData)
    {
        $this->data = $configData;
    }

    /**
     * {@inheritdoc}
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
     */
    public function getTopicHandlers($topicName)
    {
        $topicData = $this->getTopic($topicName);
        return $topicData[self::TOPIC_HANDLERS];
    }

    /**
     * {@inheritdoc}
     */
    public function getTopics()
    {
        return $this->data->get(self::TOPICS) ?: [];
    }
}
