<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     * Parameter for set mode command.
     */
    const PARAM_SET_MODE = 'indexer:set-mode';

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

    /**
     * Run set mode. Example of indexers array:
     * [
     *      [0] => ['indexer' => 'category_flat_data', 'mode' => 'schedule'],
     *      [1] => ['indexer' => 'catalogrule_product', 'mode' => 'realtime']
     * ]
     *
     * @param array $indexers
     * @return void
     */
    public function setMode(array $indexers)
    {
        foreach ($indexers as $indexer) {
            parent::execute(Indexer::PARAM_SET_MODE . ' ' . $indexer['mode'] . ' ' . $indexer['indexer']);
        }
    }
}
