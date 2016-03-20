<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Config;

use Magento\Framework\ObjectManagerInterface;

class ViewFactory
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * Create new view object
     *
     * @param array $arguments
     * @return \Magento\Framework\Config\View
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function create(array $arguments = [])
    {
        $viewConfigArguments = [];

        if (isset($arguments['themeModel']) && isset($arguments['area'])) {
            if (!($arguments['themeModel'] instanceof \Magento\Framework\View\Design\ThemeInterface)) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    new \Magento\Framework\Phrase('%1 doesn\'t implement ThemeInterface', [$arguments['themeModel']])
                );
            }
            /** @var \Magento\Theme\Model\View\Design $design */
            $design = $this->objectManager->create('Magento\Theme\Model\View\Design');
            $design->setDesignTheme($arguments['themeModel'], $arguments['area']);
            /** @var \Magento\Framework\Config\FileResolver $fileResolver */
            $fileResolver = $this->objectManager->create(
                'Magento\Framework\Config\FileResolver',
                [
                    'designInterface' => $design,
                ]
            );
            $viewConfigArguments['fileResolver'] = $fileResolver;
        }

        return $this->objectManager->create(
            'Magento\Framework\Config\View',
            $viewConfigArguments
        );
    }
}
