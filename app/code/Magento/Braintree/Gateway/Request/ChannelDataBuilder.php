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
 * @since 2.1.0
 */
class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var string
     * @since 2.1.0
     */
    private static $channel = 'channel';

    /**
     * @var string
     * @since 2.1.0
     */
    private static $channelValue = 'Magento2_Cart_%s_BT';

    /**
     * @var ProductMetadataInterface
     * @since 2.1.0
     */
    private $productMetadata;

    /**
     * @var Config
     * @since 2.2.0
     */
    private $config;

    /**
     * Constructor
     *
     * @param ProductMetadataInterface $productMetadata
     * @param Config $config
     * @since 2.1.0
     */
    public function __construct(ProductMetadataInterface $productMetadata, Config $config = null)
    {
        $this->productMetadata = $productMetadata;
        $this->config = $config ?: ObjectManager::getInstance()->get(Config::class);
    }

    /**
     * @inheritdoc
     * @since 2.1.0
     */
    public function build(array $buildSubject)
    {
        $channel = $this->config->getValue('channel');
        return [
            self::$channel => $channel ?: sprintf(self::$channelValue, $this->productMetadata->getEdition())
        ];
    }
}
