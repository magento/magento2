<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Braintree\Gateway\Request;

use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Framework\App\ProductMetadataInterface;

/**
 * Class BnCodeDataBuilder
 */
class ChannelDataBuilder implements BuilderInterface
{
    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var string
     */
    private static $channel = 'channel';

    /**
     * @var string
     */
    private static $channelValue = 'Magento2_Cart_%s_BT';

    /**
     * Constructor
     *
     * @param ProductMetadataInterface $productMetadata
     */
    public function __construct(ProductMetadataInterface $productMetadata)
    {
        $this->productMetadata = $productMetadata;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject)
    {
        return [
            self::$channel => sprintf(self::$channelValue, $this->productMetadata->getEdition())
        ];
    }
}
