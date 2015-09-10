<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Ui\Component\Listing\Attribute;

interface SearchCriteriaBuilderInterface
{
    /**
     * @return \Magento\Framework\Api\SearchCriteria
     */
    public function build();
}
