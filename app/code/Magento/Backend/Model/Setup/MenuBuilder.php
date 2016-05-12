<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\Setup;

use Magento\Backend\Model\Menu;
use Magento\Backend\Model\Menu\Builder;
use Magento\Framework\App\DocRoot;

/**
 * Plugin class to remove web setup wizard from menu if application root is pub/ and no setup url variable is specified.
 */
class MenuBuilder
{

    /**
     * @var DocRoot
     */
    protected $docRoot;

    /**
     * MenuBuilder constructor.
     *
     * @param DocRoot $docRoot
     */
    public function __construct(DocRoot $docRoot)
    {
        $this->docRoot = $docRoot;
    }

    /**
     * Removes 'Web Setup Wizard' from the menu if doc root is pub and no setup url variable is specified.
     *
     * @param Builder $subject
     * @param Menu $menu
     * @return Menu
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetResult(Builder $subject, Menu $menu)
    {
        if ($this->docRoot->hasThisSubDir('pub', 'setup')) {
            $menu->remove('Magento_Backend::setup_wizard');
        }
        return $menu;
    }
}
