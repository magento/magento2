<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Data\Test\Unit\Stub;

use Magento\Framework\Api\CriteriaInterface;
use Magento\Framework\Data\AbstractSearchCriteriaBuilder;

class SearchCriteriaBuilder extends AbstractSearchCriteriaBuilder
{
    /**
     * @return string|void
     */
    public function init()
    {
        $this->resultObjectInterface = CriteriaInterface::class;
    }
}
