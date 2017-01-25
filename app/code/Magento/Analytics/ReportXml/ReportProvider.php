<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml;

use \Magento\Framework\App\ResourceConnection;

class ReportProvider
{
    /**
     * @var QueryFactory
     */
    private $queryFactory;
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(
        QueryFactory $queryFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->queryFactory = $queryFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function getReport($name, $criteria)
    {
        
    }
}
