<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Class \Magento\Directory\Model\Country\Postcode\Config
 *
 */
class Config implements ConfigInterface
{
    /**
     * @var Config\Data
     */
    protected $dataStorage;

    /**
     * @param Config\Data $dataStorage
     */
    public function __construct(\Magento\Directory\Model\Country\Postcode\Config\Data $dataStorage)
    {
        $this->dataStorage = $dataStorage;
    }

    /**
     * {@inheritdoc}
     */
    public function getPostCodes()
    {
        return $this->dataStorage->get();
    }
}
