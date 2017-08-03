<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Class \Magento\Directory\Model\Country\Postcode\Config
 *
 * @since 2.0.0
 */
class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     * @since 2.0.0
     */
    protected $dataStorage;

    /**
     * @param Config\Data $dataStorage
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\Country\Postcode\Config\Data $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function getPostCodes()
    {
        return $this->dataStorage->get();
    }
}
