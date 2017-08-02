<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Mview\View;

/**
 * Interface \Magento\Framework\Mview\View\ChangelogInterface
 *
 * @since 2.0.0
 */
interface ChangelogInterface
{
    /**
     * Create changelog table
     *
     * @return boolean
     * @since 2.0.0
     */
    public function create();

    /**
     * Drop changelog table
     *
     * @return boolean
     * @since 2.0.0
     */
    public function drop();

    /**
     * Clear changelog by version_id
     *
     * @param int $versionId
     * @return bool
     * @since 2.0.0
     */
    public function clear($versionId);

    /**
     * Retrieve entity ids by range [$fromVersionId..$toVersionId]
     *
     * @param integer $fromVersionId
     * @param integer $toVersionId
     * @return int[]
     * @since 2.0.0
     */
    public function getList($fromVersionId, $toVersionId);

    /**
     * Get maximum version_id from changelog
     *
     * @return int
     * @since 2.0.0
     */
    public function getVersion();

    /**
     * Get changlog name
     *
     * @return string
     * @since 2.0.0
     */
    public function getName();

    /**
     * Get changlog entity column name
     *
     * @return string
     * @since 2.0.0
     */
    public function getColumnName();

    /**
     * Set view's identifier
     *
     * @param string $viewId
     * @return ChangelogInterface
     * @since 2.0.0
     */
    public function setViewId($viewId);

    /**
     * Get view's identifier
     *
     * @return string
     * @since 2.0.0
     */
    public function getViewId();
}
