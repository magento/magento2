<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Mtf\Util\Command\Cli;

use Magento\Mtf\Util\Command\Cli;

/**
 * Reindex of Magento processing.
 */
class Indexer extends Cli
{
    /**
     * Parameter for reindex indexers.
     */
    const PARAM_INDEXER_REINDEX = 'indexer:reindex';

    /**
     * Indexer index.
     *
     * @return void
     */
    public function reindex()
    {
        parent::execute('indexer:reindex');
    }
}
