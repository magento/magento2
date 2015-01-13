<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Translation\Model;

class Js
{
    /**
     * Translation data
     *
     * @var string[]
     */
    protected $translateData;

    /**
     * @param Js\DataProviderInterface[] $dataProviders
     */
    public function __construct(array $dataProviders)
    {
        /** @var $dataProvider Js\DataProviderInterface */
        foreach ($dataProviders as $dataProvider) {
            foreach ($dataProvider->getData() as $key => $translatedText) {
                if ($key !== $translatedText) {
                    $this->translateData[$key] = $translatedText;
                }
            }
        }
    }

    /**
     * Get translated data
     *
     * @return string[]
     */
    public function getTranslateData()
    {
        return $this->translateData;
    }
}
