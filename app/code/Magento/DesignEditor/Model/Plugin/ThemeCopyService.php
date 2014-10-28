<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\DesignEditor\Model\Plugin;

class ThemeCopyService
{
    /**
     * @var \Magento\DesignEditor\Model\Theme\ChangeFactory
     */
    protected $_themeChangeFactory;

    /**
     * @param \Magento\DesignEditor\Model\Theme\ChangeFactory $themeChangeFactory
     */
    public function __construct(\Magento\DesignEditor\Model\Theme\ChangeFactory $themeChangeFactory)
    {
        $this->_themeChangeFactory = $themeChangeFactory;
    }

    /**
     * Copy additional information about theme change time
     *
     * @param \Magento\Theme\Model\CopyService $subject
     * @param callable $proceed
     * @param \Magento\Framework\View\Design\ThemeInterface $source
     * @param \Magento\Framework\View\Design\ThemeInterface $target
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCopy(
        \Magento\Theme\Model\CopyService $subject,
        \Closure $proceed,
        \Magento\Framework\View\Design\ThemeInterface $source,
        \Magento\Framework\View\Design\ThemeInterface $target
    ) {
        $proceed($source, $target);
        if ($source && $target) {
            /** @var $sourceChange \Magento\DesignEditor\Model\Theme\Change */
            $sourceChange = $this->_themeChangeFactory->create();
            $sourceChange->loadByThemeId($source->getId());
            /** @var $targetChange \Magento\DesignEditor\Model\Theme\Change */
            $targetChange = $this->_themeChangeFactory->create();
            $targetChange->loadByThemeId($target->getId());

            if ($sourceChange->getId()) {
                $targetChange->setThemeId($target->getId());
                $targetChange->setChangeTime($sourceChange->getChangeTime());
                $targetChange->save();
            } elseif ($targetChange->getId()) {
                $targetChange->delete();
            }
        }
    }
}
