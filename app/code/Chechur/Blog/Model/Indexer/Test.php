<?php
declare(strict_types=1);

namespace Chechur\Blog\Model\Indexer;

class Test implements \Magento\Framework\Indexer\ActionInterface, \Magento\Framework\Mview\ActionInterface
{
    /*
     * Used by mview, allows process indexer in the "Update on schedule" mode
     */
    public function execute($ids)
    {

        //code here!
    }

    /*
     * Will take all of the data and reindex
     * Will run when reindex via command line
     */
    public function executeFull()
    {
        //code here!
    }

    /*
     * Works with a set of entity changed (may be massaction)
     */
    public function executeList(array $ids)
    {
        //code here!
    }

    /*
     * Works in runtime for a single entity using plugins
     */
    public function executeRow($id)
    {
        //code here!
    }
}
