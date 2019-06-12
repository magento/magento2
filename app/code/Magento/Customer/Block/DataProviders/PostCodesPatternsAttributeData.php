<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\DataProviders;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Directory\Model\Country\Postcode\Config as PostCodeConfig;

/**
 * Provides postcodes patterns into template.
 */
class PostCodesPatternsAttributeData implements ArgumentInterface
{
    /**
     * @var PostCodeConfig
     */
    private $postCodeConfig;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Constructor
     *
     * @param PostCodeConfig $postCodeConfig
     * @param SerializerInterface $serializer
     */
    public function __construct(PostCodeConfig $postCodeConfig, SerializerInterface $serializer)
    {
        $this->postCodeConfig = $postCodeConfig;
        $this->serializer = $serializer;
    }

    /**
     * Get serialized post codes
     *
     * @return string
     */
    public function getSerializedPostCodes(): string
    {
        return $this->serializer->serialize($this->postCodeConfig->getPostCodes());
    }
}
