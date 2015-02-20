<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset\Bundle;

use Magento\Framework\View\ConfigInterface;
use Magento\Framework\View\Asset\LocalInterface;
use Magento\Framework\View\Asset\ContextInterface;
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
     * @var ConfigInterface
     */
    protected $viewConfig;

    /**
     * @var ContextInterface
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
     * @param ConfigInterface $config
     * @param ListInterface $themeList
     */
    public function __construct(
        ConfigInterface $config,
        ListInterface $themeList
    ) {
        $this->viewConfig = $config;
        $this->themeList = $themeList;
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
     * @param LocalInterface[] $assets
     * @return LocalInterface[]
     */
    public function resolve($assets)
    {
        $this->context = reset($assets)->getContext();
        $bundleSize = $this->getBundleSize();

        $currentSize = 0;
        foreach ($assets as $path => $asset) {
            $freeSpace = $bundleSize - $currentSize;
            $content = utf8_encode($asset->getContent());
            $size = $this->getContentSize($content);

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
        return $this->bundle;
    }

    /**
     * @param string $content
     * @param string $path
     * @param int|float $freeSpace
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
     * @param string $content
     * @param string $path
     * @param int|float $freeSpace
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
     * @param LocalInterface[] $bundle
     * @return LocalInterface[]
     */
    public function appendHtmlPart($bundle)
    {
        if (!(isset($bundle[0]) && isset($bundle[1]))) {
            return false;
        }
        if (!$this->context) {
            $this->context = reset($bundle[0])->getContext();
        }

        $bundleSize = $this->getBundleSize();
        if (!$bundleSize) {
            return [array_merge($bundle[0], $bundle[1])];
        }
        return $bundle;
    }
}
