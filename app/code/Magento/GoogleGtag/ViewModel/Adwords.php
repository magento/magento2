<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GoogleGtag\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\GoogleGtag\Model\Config\GtagConfig;

class Adwords implements ArgumentInterface
{
    /**
     * @var GtagConfig
     */
    private $gtagConfig;

    /**
     * @param GtagConfig $gtagConfig
     */
    public function __construct(GtagConfig $gtagConfig)
    {
        $this->gtagConfig = $gtagConfig;
    }

    /**
     * Is Google AdWords active
     *
     * @return bool
     */
    public function isGoogleAdwordsActive(): bool
    {
        return $this->gtagConfig->isGoogleAdwordsActive();
    }

    /**
     * Is Google AdWords congifurable
     *
     * @return bool
     */
    public function isGoogleAdwordsConfigurable(): bool
    {
        return $this->gtagConfig->isGoogleAdwordsConfigurable();
    }

    /**
     * Get Google AdWords conversion id
     *
     * @return string
     */
    public function getConversionId(): string
    {
        return $this->gtagConfig->getConversionId();
    }

    /**
     * Get Google AdWords conversion label
     *
     * @return string
     */
    public function getConversionLabel(): string
    {
        return $this->gtagConfig->getConversionLabel();
    }

    /**
     * Get conversion js src
     *
     * @return string
     */
    public function getConversionGtagGlobalSiteTagSrc(): string
    {
        return $this->gtagConfig->getConversionGtagGlobalSiteTagSrc();
    }
}
