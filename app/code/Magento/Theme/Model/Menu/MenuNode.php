<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Theme\Api\Data\MenuNodeInterface;

/**
 * Data container for menu item.
 */
class MenuNode implements MenuNodeInterface
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $url;

    /**
     * @var array
     */
    private $children;

    /**
     * Instantiate menu node.
     *
     * Menu node instantiated ready to use. Please avoid node modification with setters after object creation.
     * Setters are implemented because of Web API framework requirements.
     *
     * @param string $id
     * @param string $name
     * @param string $url
     * @param MenuNodeInterface[] $children
     */
    public function __construct(
        string $id,
        string $name,
        string $url,
        array $children = []
    ) {
        $this->setId($id);
        $this->setName($name);
        $this->setUrl($url);
        $this->setChildren($children);
    }

    /**
     * @inheritDoc
     */
    public function getId(): string
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function setId(string $id): void
    {
        $this->id = $id;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @inheritDoc
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @inheritDoc
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }

    /**
     * @inheritDoc
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     */
    public function setChildren(array $children): void
    {
        $this->children = array_map(
            function (MenuNodeInterface $node) { // ensure each child is node instance
                return $node;
            },
            $children
        );
    }
}
