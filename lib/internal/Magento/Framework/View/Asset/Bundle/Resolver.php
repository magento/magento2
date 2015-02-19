<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View;
use Magento\Framework\View\Asset;
use Magento\Tools;
use Magento\Framework\View\Design\Theme\ListInterface;

class Resolver implements ResolverInterface
{
    const VIEW_CONFIG_MODULE = 'Js_Bundle';

    const VIEW_CONFIG_BUNDLE_SIZE_NAME = 'bundle_size';

    /**
     * @var ListInterface
     */
    protected $themeList;

    /**
     * @var View\ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var Asset\ContextInterface
     */
    protected $context;

    /**
     * @var array
     */
    protected $bundle = [];

    /**
     * @var array
     */
    protected $freeSpaces = [];

    /**
     * @var int
     */
    protected $currentPart = 0;

    /**
     * @var Tools\View\Deployer\Log
     */
    protected $logger;

    /**
     * @param View\ConfigInterface $config
     * @param ListInterface $themeList
     * @param Tools\View\Deployer\Log $logger
     */
    public function __construct(
        View\ConfigInterface $config,
        ListInterface $themeList,
        Tools\View\Deployer\Log $logger
    ) {
        $this->viewConfig = $config;
        $this->themeList = $themeList;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Config\View
     */
    protected function getConfig()
    {
        return $this->viewConfig->getViewConfig([
            'area' => $this->context->getAreaCode(),
            'themeModel' => $this->themeList->getThemeByFullPath(
                $this->context->getAreaCode() . '/' . $this->context->getThemePath()
            )
        ]);
    }

    /**
     * @return float|int|string
     */
    protected function getBundleSize()
    {
        $size = $this->getConfig()->getVarValue(self::VIEW_CONFIG_MODULE, self::VIEW_CONFIG_BUNDLE_SIZE_NAME);
        $unit = preg_replace('/[^a-zA-Z]+/', '', $size);
        $unit = strtoupper($unit);
        switch ($unit) {
            case 'KB':
                break;
            case 'MB':
                $size = (int)$size * 1024;
                break;
            default:
                $size = (int)$size / 1024;
        }
        return $size;
    }

    /**
     * @param Asset\LocalInterface[] $assets
     * @return Asset\LocalInterface[]
     */
    public function resolve($assets)
    {
        $this->context = reset($assets)->getContext();
        $bundleSize = $this->getBundleSize();

        $currentSize = 0;
        $totalSize = 0;
        foreach ($assets as $path => $asset) {
            $freeSpace = $bundleSize - $currentSize;
            $content = utf8_encode($asset->getContent());
            $size = $this->getContentSize($content);
            $totalSize += $size;
            if ($bundleSize == 0) {
                $this->bundle[0][$path] = $content;
                continue;
            }

            if ($size < $freeSpace) {
                $currentSize += $size;
                $this->appendInCurrentPart($content, $path, round($freeSpace - $size, 2));
            } else {
                if (!$this->appendInPreviousParts($content, $path)) {
                    $currentSize = $size;
                    $this->appendIntoNewPart($content, $path, round($bundleSize - $size, 2));
                }
            }
        }
        $this->logBundleSize($totalSize);
        return $this->bundle;
    }

    protected function logBundleSize($totalSize)
    {
        $this->logger->logMessage(
            '=== ' . $this->context->getAreaCode() .
            ' -> ' . $this->context->getThemePath() .
            ' -> ' . $this->context->getLocaleCode() . ' ==='
        );
        $this->logger->logMessage('Total bundle size: ' . round($totalSize, 2) . " KB\n");
    }

    /**
     * @param $content
     * @param $path
     * @param $freeSpace
     *
     * @return void
     */
    protected function appendInCurrentPart($content, $path, $freeSpace)
    {
        $this->bundle[$this->currentPart][$path] = $content;
        $this->freeSpaces[$this->currentPart]['free-space'] = $freeSpace;
    }

    /**
     * @param string $content
     * @param string $path
     *
     * @return bool
     */
    protected function appendInPreviousParts($content, $path)
    {
        $contentSize = $this->getContentSize($content);
        for ($i = 0; $i < $this->currentPart; $i++) {
            if (isset($this->freeSpaces[$i]) && $this->freeSpaces[$i]['free-space'] >= $contentSize) {
                $this->bundle[$i][$path] = $content;
                $this->freeSpaces[$i]['free-space'] -= $contentSize;
                return true;
            }
        }
        return false;
    }

    /**
     * @param $content
     * @param $path
     * @param $freeSpace
     *
     * @return void
     */
    protected function appendIntoNewPart($content, $path, $freeSpace)
    {
        $this->currentPart++;
        $this->freeSpaces[$this->currentPart]['free-space'] = $freeSpace;
        $this->bundle[$this->currentPart][$path] = $content;
    }

    /**
     * @param string $content
     * @return int
     */
    protected function getContentSize($content)
    {
        return mb_strlen($content, 'utf-8') / 1024;
    }

    /**
     * @param \Magento\Framework\View\Asset\LocalInterface[] $bundle
     * @return \Magento\Framework\View\Asset\LocalInterface[]
     */
    public function appendHtmlPart($bundle)
    {
        $bundleSize = $this->getBundleSize();
        if (!$bundleSize) {
            $bundle[0] .= $bundle[1];
            return [$bundle[0]];
        }
        return $bundle;
    }
}
