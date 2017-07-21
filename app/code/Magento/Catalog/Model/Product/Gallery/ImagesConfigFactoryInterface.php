<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Gallery;

interface ImagesConfigFactoryInterface
{
    /**
     * Create Gallery Images Config Collection from array
     *
     * @param array $imagesConfig
     * @param array $data
     *
     * @return \Magento\Framework\Data\Collection
     */
    public function create(array $imagesConfig, array $data = []);
}
