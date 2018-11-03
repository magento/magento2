<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Theme\Api\GetMenuInterface;
use Magento\Theme\Api\Data\MenuNodeInterface;

/**
 * GetMenuInterface implementation which returns menu items passed during instantiation.
 *
 * @api
 */
class GetPredefinedMenu implements GetMenuInterface
{
    /**
     * @var MenuNodeInterface[]
     */
    private $menuNodes;

    /**
     * GetPredefinedMenu constructor.
     *
     * @param array $menuNodes
     */
    public function __construct(array $menuNodes = [])
    {
        $this->menuNodes = array_map(
            function (MenuNodeInterface $menuNode) {
                return $menuNode;
            },
            $menuNodes
        );
    }

    /**
     * @inheritDoc
     */
    public function execute(): array
    {
        return array_values($this->menuNodes);
    }
}
