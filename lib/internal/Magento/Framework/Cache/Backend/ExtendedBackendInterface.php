<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Backend;

/**
 * Extended cache backend interface
 *
 * Adds advanced cache operations like querying IDs and tags.
 */
interface ExtendedBackendInterface extends BackendInterface
{
    /**
     * Return an array of stored cache ids
     *
     * @return array Array of stored cache ids (string)
     */
    public function getIds();

    /**
     * Return an array of stored tags
     *
     * @return array Array of stored tags (string)
     */
    public function getTags();

    /**
     * Return an array of stored cache ids which match given tags
     *
     * In case of multiple tags, an intersection of all matching ids is returned
     *
     * @param array $tags Array of tags
     * @return array Array of matching cache ids (string)
     */
    public function getIdsMatchingTags($tags = []);

    /**
     * Return an array of stored cache ids which don't match given tags
     *
     * In case of multiple tags, a union of all non-matching ids is returned
     *
     * @param array $tags Array of tags
     * @return array Array of not matching cache ids (string)
     */
    public function getIdsNotMatchingTags($tags = []);

    /**
     * Return an array of stored cache ids which match any given tags
     *
     * In case of multiple tags, a union of all matching ids is returned
     *
     * @param array $tags Array of tags
     * @return array Array of matching cache ids (string)
     */
    public function getIdsMatchingAnyTags($tags = []);

    /**
     * Return the filling percentage of the backend storage
     *
     * @return int An integer between 0 and 100
     */
    public function getFillingPercentage();

    /**
     * Return an associative array of metadatas for the given cache id
     *
     * The array must include these keys:
     * - expire: int, expiration timestamp
     * - tags: array, array of associated tags
     * - mtime: int, last modification timestamp
     *
     * @param string $id Cache id
     * @return array|false Associative array of metadatas or false if cache doesn't exist
     */
    public function getMetadatas($id);

    /**
     * Give (if possible) an extra lifetime to the given cache id
     *
     * @param string $id Cache id
     * @param int $extraLifetime Extra lifetime (in seconds)
     * @return bool True if ok
     */
    public function touch($id, $extraLifetime);

    /**
     * Return an associative array of capabilities of the backend
     *
     * The array must include these keys:
     * - automatic_cleaning: bool, automatic cache cleaning
     * - tags: bool, tag support
     * - expired_read: bool, expired cache entries can be read
     * - priority: bool, priority support
     * - infinite_lifetime: bool, infinite lifetime support
     * - get_list: bool, list of cache ids/tags support
     *
     * @return array Associative array of capabilities
     */
    public function getCapabilities();
}
