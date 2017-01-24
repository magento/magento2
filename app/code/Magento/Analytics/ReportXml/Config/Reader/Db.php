<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\ReportXml\Config\Reader;

use Magento\Framework\Config\ReaderInterface;

/**
 * Class Db
 *
 * Reader for config stored in database
 */
class Db implements ReaderInterface
{

    /**
     * Load configuration scope
     *
     * @param string|null $scope
     * @return array
     */
    public function read($scope = null)
    {
        return [];
    }
}
