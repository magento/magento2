<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
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
