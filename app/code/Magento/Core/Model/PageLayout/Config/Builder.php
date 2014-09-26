<?php
/**
 * Magento validator config factory
 *
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
namespace Magento\Core\Model\PageLayout\Config;

/**
 * Page layout config builder
 */
class Builder
{
    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\View\PageLayout\File\Collector\Aggregated
     */
    protected $fileCollector;

    /**
     * @var \Magento\Core\Model\Resource\Theme\Collection
     */
    protected $themeCollection;

    /**
     * @param \Magento\Framework\ObjectManager $objectManager
     * @param \Magento\Framework\View\PageLayout\File\Collector\Aggregated $fileCollector
     * @param \Magento\Core\Model\Resource\Theme\Collection $themeCollection
     */
    public function __construct(
        \Magento\Framework\ObjectManager $objectManager,
        \Magento\Framework\View\PageLayout\File\Collector\Aggregated $fileCollector,
        \Magento\Core\Model\Resource\Theme\Collection $themeCollection
    ) {
        $this->objectManager = $objectManager;
        $this->fileCollector = $fileCollector;
        $this->themeCollection = $themeCollection;
    }

    /**
     * @return \Magento\Framework\View\PageLayout\Config
     */
    public function getPageLayoutsConfig()
    {
        return $this->objectManager->create(
            'Magento\Framework\View\PageLayout\Config',
            ['configFiles' => $this->getConfigFiles()]
        );
    }

    /**
     * @return array
     */
    protected function getConfigFiles()
    {
        $configFiles = [];
        foreach ($this->themeCollection->loadRegisteredThemes() as $theme) {
            $configFiles = array_merge($configFiles, $this->fileCollector->getFilesContent($theme, 'layouts.xml'));
        }

        return $configFiles;
    }
}
