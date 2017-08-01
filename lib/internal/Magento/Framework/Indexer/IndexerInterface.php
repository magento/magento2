<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Indexer;

/**
 * Indexer
 *
 * @api
 * @since 2.0.0
 */
interface IndexerInterface
{
    /**
     * Return indexer ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getId();

    /**
     * Return indexer's view ID
     *
     * @return string
     * @since 2.0.0
     */
    public function getViewId();

    /**
     * Return indexer action class
     *
     * @return string
     * @since 2.0.0
     */
    public function getActionClass();

    /**
     * Return indexer title
     *
     * @return string
     * @since 2.0.0
     */
    public function getTitle();

    /**
     * Return indexer description
     *
     * @return string
     * @since 2.0.0
     */
    public function getDescription();

    /**
     * Return indexer fields
     *
     * @return array
     * @since 2.0.0
     */
    public function getFields();

    /**
     * Return indexer sources
     *
     * @return array
     * @since 2.0.0
     */
    public function getSources();

    /**
     * Return indexer handlers
     *
     * @return array
     * @since 2.0.0
     */
    public function getHandlers();

    /**
     * Fill indexer data from config
     *
     * @param string $indexerId
     * @return IndexerInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function load($indexerId);

    /**
     * Return related view object
     *
     * @return \Magento\Framework\Mview\ViewInterface
     * @since 2.0.0
     */
    public function getView();

    /**
     * Return related state object
     *
     * @return StateInterface
     * @since 2.0.0
     */
    public function getState();

    /**
     * Set indexer state object
     *
     * @param StateInterface $state
     * @return IndexerInterface
     * @since 2.0.0
     */
    public function setState(StateInterface $state);

    /**
     * Check whether indexer is run by schedule
     *
     * @return bool
     * @since 2.0.0
     */
    public function isScheduled();

    /**
     * Turn scheduled mode on/off
     *
     * @param bool $scheduled
     * @return void
     * @since 2.0.0
     */
    public function setScheduled($scheduled);

    /**
     * Check whether indexer is valid
     *
     * @return bool
     * @since 2.0.0
     */
    public function isValid();

    /**
     * Check whether indexer is invalid
     *
     * @return bool
     * @since 2.0.0
     */
    public function isInvalid();

    /**
     * Check whether indexer is working
     *
     * @return bool
     * @since 2.0.0
     */
    public function isWorking();

    /**
     * Set indexer invalid
     *
     * @return void
     * @since 2.0.0
     */
    public function invalidate();

    /**
     * Return indexer status
     *
     * @return string
     * @since 2.0.0
     */
    public function getStatus();

    /**
     * Return indexer or mview latest updated time
     *
     * @return string
     * @since 2.0.0
     */
    public function getLatestUpdated();

    /**
     * Regenerate full index
     *
     * @return void
     * @throws \Exception
     * @since 2.0.0
     */
    public function reindexAll();

    /**
     * Regenerate one row in index by ID
     *
     * @param int $id
     * @return void
     * @since 2.0.0
     */
    public function reindexRow($id);

    /**
     * Regenerate rows in index by ID list
     *
     * @param int[] $ids
     * @return void
     * @since 2.0.0
     */
    public function reindexList($ids);
}
