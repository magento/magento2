<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component\Listing;

/**
 * Class OptionsInterface
 */
interface OptionsInterface
{
    /**
     * Get options
     *
     * @param array $options
     * @return array
     */
    public function getOptions(array $options = []);
}
