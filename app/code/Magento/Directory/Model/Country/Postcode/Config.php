<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

class Config 
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
     * @return array|mixed|null
     */
    public function getPostCodes()
    {
        return $this->dataStorage->get();
    }
}
