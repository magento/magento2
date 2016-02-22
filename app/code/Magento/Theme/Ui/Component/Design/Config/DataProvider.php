<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Ui\Component\Design\Config;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $data = parent::getData();
        foreach ($data['items'] as & $item) {
            $item += ['default' => __('Global')];
        }

        return $data;
    }
}
