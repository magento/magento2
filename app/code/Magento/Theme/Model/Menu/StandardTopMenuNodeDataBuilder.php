<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Framework\App\Request\Http;
use Magento\Framework\App\RequestInterface;
use Magento\Theme\Api\Data\MenuNodeInterface;

/**
 * Standard top menu data builder which transform basic interface to hash map.
 */
class StandardTopMenuNodeDataBuilder implements TopMenuNodeDataBuilderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * StandardTopMenuNodeDataBuilder constructor.
     *
     * @param RequestInterface $request
     */
    public function __construct(
        RequestInterface $request
    ) {
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function isAcceptable(MenuNodeInterface $menuNode): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function buildData(MenuNodeInterface $menuNode): array
    {
        $data = [
            'name' => $menuNode->getName(),
            'id' => $menuNode->getId(),
            'url' => $menuNode->getUrl(),
            'is_active' => $this->isMenuNodeActive($menuNode),
            'has_active' => $this->menuNodeHasActiveChild($menuNode),
        ];
        return $data;
    }

    /**
     * Checks that menu node is active.
     *
     * @param MenuNodeInterface $menuNode
     * @return bool
     */
    private function isMenuNodeActive(MenuNodeInterface $menuNode): bool
    {
        if ($this->isUrlMatchRequest($menuNode->getUrl())) {
            return true;
        }

        return false;
    }

    /**
     * Checks that active menu item is one of nested item.
     *
     * @param MenuNodeInterface $menuNode
     * @return bool
     */
    private function menuNodeHasActiveChild(MenuNodeInterface $menuNode): bool
    {
        foreach ($menuNode->getChildren() as $childNode) {
            if ($this->isMenuNodeActive($childNode) || $this->menuNodeHasActiveChild($childNode)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Checks that menu item URL is URL of current HTTP request.
     *
     * @param string $url
     * @return bool
     */
    private function isUrlMatchRequest(string $url): bool
    {
        if (!$this->request instanceof Http) {
            return false;
        }

        return $url === $this->request->getPathInfo();
    }
}
