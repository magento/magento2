<?php
/**
 * @copyright Copyright (c) 2015 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\View\Asset;

/**
 * Bundle model
 */
class Bundle
{
    const XML_PATH_NUMBER_OF_BUNDLES = '';

    const BUNDLE_TYPE_JS = 'js';

    const BUNDLE_TYPE_HTML = 'html';
    
    /** @var string */
    protected $bundlePath;

    /** @var int */
    protected $numberOfBundles;

    /** @var array */
    protected $assets = [];

    /** @var array */
    protected $bundle = [];

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /** @var string */
    protected $type;

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

    public static function isValidType($type)
    {
        return in_array($type, self::$availableTypes);
    }

    function __construct(
        \Magento\Framework\View\Asset\Bundle\ConfigInterface $config,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->init();
    }

    protected function init()
    {
        $this->numberOfBundles = 1;
        //$this->numberOfBundles = $this->scopeConfig->getValue(
        //    self::XML_PATH_NUMBER_OF_BUNDLES,
        //    \Magento\Framework\App\ScopeInterface::SCOPE_DEFAULT
        //);
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $path
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
     */
    public function addAsset(LocalInterface $asset)
    {
        $this->assets[$this->getAssetKey($asset)] = $asset;
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
     */
    protected function divide()
    {
        $perBundlePart = ceil(count($this->assets) / $this->numberOfBundles);
        $this->bundle = array_chunk($this->assets, $perBundlePart, true);
    }

    /**
     * Fill bundle with real content
     */
    protected function fill()
    {
        foreach ($this->assets as $path => $asset) {
            $this->assets[$path] = utf8_encode($asset->getContent());
        }
    }

    /**
     * Convert bundle content to json
     */
    protected function toJson()
    {
        foreach ($this->bundle as &$part) {
            $part = json_encode($part, JSON_UNESCAPED_SLASHES);
        }
    }

    /**
     * Prepare bundle for executing in js
     */
    protected function wrapp()
    {
        foreach ($this->bundle as &$part) {
           $part = "require.config({\n" .
            "    config: {\n" .
            "        '" . $this->getJsName() . "':" . $part . "\n" .
            "    }\n" .
            "});\n";
        }
    }

    public function addInitJs()
    {
        if ($this->getType() != self::BUNDLE_TYPE_HTML) {
            return false;
        }
        foreach ($this->bundle as &$part) {
            $part = "require.config({\n" .
                    "    bundles: {\n" .
                    "        'mage/requirejs/static': [\n" .
                    "            'jsbuild',\n" .
                    "            'buildTools',\n" .
                    "            'text'\n" .
                    "        ]\n" .
                    "    },\n" .
                    "    deps: [\n" .
                    "        'jsbuild'\n" .
                    "    ]\n" .
                    "});\n" .
                    $part;
            break;
        }
    }

    public function getJsName()
    {
        return $this->bundleNames[$this->getType()];
    }

    /**
     * Get bundle content
     *
     * @return array
     */
    public function getContent()
    {
        $this->prepare();
        return $this->bundle;
    }

    /**
     * @return void
     */
    protected function prepare()
    {
        $this->fill();
        $this->divide();
        $this->toJson();
        $this->wrapp();
        $this->addInitJs();
    }
}
