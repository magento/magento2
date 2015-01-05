<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Downloadable\Api;

interface LinkManagementInterface
{
    /**
     * List of samples for downloadable product
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\SampleInterface[]
     */
    public function getSamples($productSku);

    /**
     * List of links with associated samples
     *
     * @param string $productSku
     * @return \Magento\Downloadable\Api\Data\LinkInterface[]
     */
    public function getLinks($productSku);


}
