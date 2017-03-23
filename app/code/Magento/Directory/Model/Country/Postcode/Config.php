<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

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
