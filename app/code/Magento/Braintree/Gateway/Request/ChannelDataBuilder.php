<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Payment\Gateway\Config\Config;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Class BnCodeDataBuilder
 */
class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string
     */
    private static $channel = 'channel';

    /**
     * @var string
     */
    private static $channelValue = 'Magento2_Cart_%s_BT';

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var Config
     */
    private $config;

    /**
     * Constructor
     *
     * @param ProductMetadataInterface $productMetadata
     * @param Config $config
     */
    public function __construct(ProductMetadataInterface $productMetadata, Config $config = null)
    {
        $this->productMetadata = $productMetadata;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        $channel = $this->config->getValue('channel');
        return [
            self::$channel => $channel ?: sprintf(self::$channelValue, $this->productMetadata->getEdition())
        ];
    }
}
