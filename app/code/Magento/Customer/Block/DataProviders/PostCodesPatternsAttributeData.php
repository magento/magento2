<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Block\DataProviders;

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
     * Constructor
     *
     * @param PostCodeConfig $postCodeConfig
     */
    public function __construct(PostCodeConfig $postCodeConfig)
    {
        $this->postCodeConfig = $postCodeConfig;
    }

    /**
     * Get post codes in json format
     *
     * @return string
     */
    public function getPostCodesJson(): string
    {
        return json_encode($this->postCodeConfig->getPostCodes());
    }
}
