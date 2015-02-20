<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Asset;

use Magento\Framework\App;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Asset\Bundle\ResolverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Bundle
{
    const BUNDLE_TYPE_JS = 'js';

    const BUNDLE_TYPE_HTML = 'html';

    /**
     * @var string
     */
    protected $bundlePath;

    /**
     * @var array
     */
    protected $assets = [];

    /**
     * @var array
     */
    protected $htmlAssets = [];

    /**
     * @var array
     */
    protected $bundle = [];

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var ResolverInterface
     */
    protected $resolver;

    /**
     * @var array
     */
    protected $bundleNames = [
        self::BUNDLE_TYPE_JS => 'jsbuild',
        self::BUNDLE_TYPE_HTML => 'text'
    ];

    /**
     * @var array
     */
    protected static $availableTypes = [self::BUNDLE_TYPE_JS, self::BUNDLE_TYPE_HTML];

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ResolverInterface $resolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->resolver = $resolver;
    }

    /**
     * @param LocalInterface $asset
     * @return bool
     */
    public static function isValid(LocalInterface $asset)
    {
        $type = $asset->getContentType();
        if (!in_array($type, self::$availableTypes)) {
            return false;
        }

        if ($type == self::BUNDLE_TYPE_HTML) {
            return $asset->getModule() !== '';
        }

        return true;
    }

    /**
     * Set bundle area
     *
     * @param ContextInterface $context
     * @return void
     */
    protected function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return ContextInterface
     */
    protected function getContext()
    {
        return $this->context;
    }

    /**
     * @param string $path
     *
     * @return void
     */
    public function setPath($path)
    {
        $this->bundlePath = $path;
    }

    /**
     * Get bundle save path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->bundlePath;
    }

    /**
     * @param LocalInterface $asset
     *
     * @return void
     */
    public function addAsset(LocalInterface $asset)
    {
        $this->setContext($asset->getContext());
        if ($asset->getContentType() == self::BUNDLE_TYPE_HTML) {
            $this->htmlAssets[$this->getAssetKey($asset)] = $asset;
        } else {
            $this->assets[$this->getAssetKey($asset)] = $asset;
        }
    }

    /**
     * Build asset key
     *
     * @param LocalInterface $asset
     * @return string
     */
    protected function getAssetKey(LocalInterface $asset)
    {
        return ($asset->getModule() == '') ? $asset->getFilePath() : $asset->getModule() . '/' . $asset->getFilePath();
    }

    /**
     * Divided bundle on small parts
     *
     * @return bool
     */
    protected function divide()
    {
        $this->bundle = $this->resolver->resolve($this->assets);
    }

    /**
     * @return void
     */
    protected function merge()
    {
        foreach ($this->htmlAssets as $path => $asset) {
            $this->htmlAssets[$path] = utf8_encode($asset->getContent());
        }
        $this->bundle[] = $this->htmlAssets;
    }

    /**
     * Prepare bundle for executing in js
     *
     * @return void
     */
    protected function prepare()
    {
        foreach ($this->bundle as $key => $part) {
            if (empty($part)) {
                continue;
            }
            $path = array_keys($part)[0];
            $partType = substr($path, strrpos($path, '.') + 1);
            $part = json_encode($part, JSON_UNESCAPED_SLASHES);
            $part = "require.config({\n" .
                "    config: {\n" .
                "        '" . $this->bundleNames[$partType] . "':" . $part . "\n" .
                "    }\n" .
                "});\n";
            $this->bundle[$key] = $part;
        }
    }

    /**
     * @return bool
     */
    protected function addInitJs()
    {
        $part = reset($this->bundle);
        $part = "require.config({\n" .
                "    bundles: {\n" .
                "        'mage/requirejs/static': [\n" .
                "            'jsbuild',\n" .
                "            'buildTools',\n" .
                "            'text',\n" .
                "            'statistician'\n" .
                "        ]\n" .
                "    },\n" .
                "    deps: [\n" .
                "        'jsbuild'\n" .
                "    ]\n" .
                "});\n" .
                $part;
        $this->bundle[0] = $part;

        return true;
    }

    /**
     * Get bundle content
     *
     * @return LocalInterface[]
     */
    public function getContent()
    {
        $this->divide();
        $this->merge();
        $this->prepare();
        $this->addInitJs();
        $this->bundle = $this->resolver->appendHtmlPart($this->bundle, $this->getContext());

        return $this->bundle;
    }
}
