<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Theme\Model\Menu;

use Magento\Theme\Api\GetMenuInterface;

/**
 * Implementation of GetMenuInterface which merge menu items from several other services.
 *
 * @api
 */
class GetCompoundMenu implements GetMenuInterface
{
    /**
     * @var GetMenuInterface[]
     */
    private $menuProviders;

    /**
     * GetCompoundMenu constructor.
     *
     * @param GetMenuInterface[] $menuProviders
     */
    public function __construct(array $menuProviders = [])
    {
        $this->menuProviders = array_map(
            function (GetMenuInterface $getMenu) {
                return $getMenu;
            },
            $menuProviders
        );
    }

    /**
     * @inheritdoc
     */
    public function execute(): array
    {
        $menuNodes = [];
        foreach ($this->menuProviders as $getMenu) {
            foreach ($getMenu->execute() as $menuNode) {
                $menuNodes[] = $menuNode;
            }
        }
        return $menuNodes;
    }
}
