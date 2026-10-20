<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Indexer\Model\ResourceModel\Mview\View;

class State extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('mview_state', 'state_id');
        $this->addUniqueField(['field' => ['view_id'], 'title' => __('State for the same view')]);
    }

    /**
     * Advance an MView cursor only while its worker is still active.
     *
     * @param string $viewId
     * @param int $versionId
     * @return int Number of updated rows.
     */
    public function updateVersionIdIfWorking(string $viewId, int $versionId): int
    {
        return $this->getConnection()->update(
            $this->getMainTable(),
            ['version_id' => $versionId],
            [
                'view_id = ?' => $viewId,
                'status = ?' => \Magento\Framework\Mview\View\StateInterface::STATUS_WORKING,
                'version_id < ?' => $versionId,
            ]
        );
    }
}
