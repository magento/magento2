<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

interface ChangelogInterface
{
    /**
     * Create changelog table
     *
     * @return boolean
     */
    public function create();

    /**
     * Drop changelog table
     *
     * @return boolean
     */
    public function drop();

    /**
     * Clear changelog by version_id
     *
     * @param int $versionId
     * @return bool
     */
    public function clear($versionId);

    /**
     * Retrieve entity ids by range [$fromVersionId..$toVersionId]
     *
     * @param integer $fromVersionId
     * @param integer $toVersionId
     * @return int[]
     */
    public function getList($fromVersionId, $toVersionId);

    /**
     * Get maximum version_id from changelog
     *
     * @return int
     */
    public function getVersion();

    /**
     * Get changlog name
     *
     * @return string
     */
    public function getName();

    /**
     * Get changlog entity column name
     *
     * @return string
     */
    public function getColumnName();

    /**
     * Set view's identifier
     *
     * @param string $viewId
     * @return ChangelogInterface
     */
    public function setViewId($viewId);

    /**
     * Get view's identifier
     *
     * @return string
     */
    public function getViewId();
}
