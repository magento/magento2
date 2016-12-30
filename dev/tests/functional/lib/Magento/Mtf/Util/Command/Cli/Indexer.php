<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Handle reindexing for tests executions.
 */
class Indexer extends Cli
{
    /**
     * Parameter for reindex command.
     */
    const PARAM_INDEXER_REINDEX = 'indexer:reindex';

    /**
     * Run reindex.
     *
     * @param array $indexes [optional]
     * @return void
     */
    public function reindex(array $indexes = [])
    {
        $params = '';
        if (!empty($indexes)) {
            $params = implode(' ', $indexes);
        }
        parent::execute(Indexer::PARAM_INDEXER_REINDEX . ' ' . $params);
    }
}
