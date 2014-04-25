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
namespace Magento\Core\Model\Theme;

class ThemeProvider implements \Magento\Framework\View\Design\Theme\ThemeProviderInterface
{
    /**
     * @var \Magento\Core\Model\Resource\Theme\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Core\Model\ThemeFactory
     */
    protected $themeFactory;

    /**
     * @param \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory
     * @param \Magento\Core\Model\ThemeFactory $themeFactory
     */
    public function __construct(
        \Magento\Core\Model\Resource\Theme\CollectionFactory $collectionFactory,
        \Magento\Core\Model\ThemeFactory $themeFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->themeFactory = $themeFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeByFullPath($fullPath)
    {
        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->collectionFactory->create();
        return $themeCollection->getThemeByFullPath($fullPath);
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeCustomizations(
        $area = \Magento\Framework\App\Area::AREA_FRONTEND,
        $type = \Magento\Framework\View\Design\ThemeInterface::TYPE_VIRTUAL
    ) {
        /** @var $themeCollection \Magento\Core\Model\Resource\Theme\Collection */
        $themeCollection = $this->collectionFactory->create();
        $themeCollection->addAreaFilter($area)->addTypeFilter($type);
        return $themeCollection;
    }

    /**
     * {@inheritdoc}
     */
    public function getThemeById($themeId)
    {
        /** @var $themeModel \Magento\Framework\View\Design\ThemeInterface */
        $themeModel = $this->themeFactory->create();
        return $themeModel->load($themeId);
    }
}
