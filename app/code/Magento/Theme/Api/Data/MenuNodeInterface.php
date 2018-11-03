<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Api\Data;

/**
 * Interface represents node of navigation menu and may contain nested nodes
 *
 * Any module may provide own implementation of this interface to add specific items to menu.
 *
 * @api
 */
interface MenuNodeInterface
{
    /**
     * Get menu node identifier.
     *
     * Menu node identifier may be used for node handling. However, there is no guarantee on identifier uniqueness.
     *
     * @return string
     */
    public function getId(): string;

    /**
     * Set menu node identifier.
     *
     * @param string $id
     * @return $this
     */
    public function setId(string $id): MenuNodeInterface;

    /**
     * Get menu node name.
     *
     * Menu node name should be human-readable and not intended to be used during nodes processing.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Set menu node name.
     *
     * @param string $name
     * @return $this
     */
    public function setName(string $name): MenuNodeInterface;

    /**
     * Get menu node URL.
     *
     * Each menu node should represent available resource.
     *
     * @return string
     */
    public function getUrl(): string;

    /**
     * Set menu node URL.
     *
     * @param string $url
     * @return MenuNodeInterface
     */
    public function setUrl(string $url): MenuNodeInterface;

    /**
     * Get nested menu nodes.
     *
     * Each node may contain nested nodes.
     * Nesting level is not limited by menu and should be properly handled during menu rendering if matter.
     *
     * @return array
     */
    public function getChildren(): array;

    /**
     * Set nested menu nodes.
     *
     * @param array $children
     * @return MenuNodeInterface
     */
    public function setChildren(array $children): MenuNodeInterface;
}
