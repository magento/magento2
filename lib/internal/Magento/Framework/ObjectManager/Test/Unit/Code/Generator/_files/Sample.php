<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager\Code\Generator;

/**
 * Class Sample for Proxy and Factory generation
 */
class Sample
{
    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @var array
     */
    private $config = [];

    /**
     * Union type attribute
     *
     * @var int|string
     */
    private int|string $attribute;

    /**
     * @param array $messages
     */
    public function setMessages(array $messages)
    {
        $this->messages = $messages;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig(): array
    {
        return $this->config;
    }

    /**
     * @param int|string $attribute
     */
    public function setAttribute(int|string $attribute)
    {
        $this->attribute = $attribute;
    }

    /**
     * @return int|string
     */
    public function getAttribute(): int|string
    {
        return $this->attribute;
    }
}
