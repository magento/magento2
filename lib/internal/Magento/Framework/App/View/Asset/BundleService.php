<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */

namespace Magento\Framework\App\View\Asset;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\View\Asset;
use Magento\Tools\View\Deployer;

class BundleService
{
    /**
     * @var string
     */
    protected $bundleName = 'bundle.js';

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $filesystem;

    /**
     * @var array
     */
    protected $bundles = [];

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /** @var array  */
    protected $excludeList = [
        'adminhtml' => [
            "mage/common.js",
            "mage/cookies.js",
            "mage/dataPost.js",
            "mage/decorate.js",
            "mage/deletable-item.js",
            "mage/dialog.js",
            "mage/dropdown.js",
            "mage/dropdowns.js",
            "mage/fieldset-controls.js",
            "mage/gallery-fullscreen.js",
            "mage/gallery.js",
            "mage/item-table.js",
            "mage/list.js",
            "mage/loader.js",
            "mage/menu.js",
            "mage/popup-window.js",
            "mage/redirect-url.js",
            "mage/sticky.js",
            "mage/terms.js",
            "mage/toggle.js",
            "mage/tooltip.js",
            "mage/translate-inline-vde.js",
            "mage/webapi.js",
            "mage/zoom.js",
            "mage/validation/dob-rule.js",
            "mage/validation/validation.js",
            "jquery/jquery.parsequery.js",
            "jquery/jquery.mobile.custom.js",
            "jquery/jquery-ui.js",
            "jquery/autocomplete/jquery.autocomplete.js",
            "matchMedia.js"
        ],
        'frontend' => [
            "mage/captcha.js",
            "mage/dropdown_old.js",
            "mage/list.js",
            "mage/loader_old.js",
            "mage/webapi.js",
            "mage/adminhtml/*",
            "mage/backeknd/*",
            "jquery/jquery-ui-1.9.2.js",
            "jquery/jquery.ba-hashchange.min.js",
            "jquery/jquery.details.js",
            "jquery/jquery.hoverIntent.js",
            "jquery/autocomplete/jquery.autocomplete.js",
            "jquery/editableMultiselect/js/jquery.editable.js",
            "jquery/editableMultiselect/js/jquery.multiselect.js",
            "jquery/farbtastic/jquery.farbtastic.js",
            "jquery/fileUploader/*",
            "jquery/jstree/*"
        ]
    ];

    /**
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->filesystem = $filesystem;
        $this->objectManager = $objectManager;
    }

    /**
     * Check if asset in exclude list
     *
     * @param $area
     * @param $key
     * @return bool
     */
    public function isExcluded($area, $key)
    {
        return in_array($key, $this->excludeList[$area]);
    }

    /**
     * @param Asset\LocalInterface $asset
     * @param array $context
     * @return bool
     */
    public function collect(Asset\LocalInterface $asset, array $context)
    {
        if (!$this->isValidAsset($asset, $context)) {
            return false;
        }

        /** @var \Magento\Framework\App\View\Asset\Bundle $bundle */
        $bundle = $this->getBundle($context);
        $bundle->addAsset($asset);

        return true;
    }

    /**
     * @param Asset\LocalInterface $asset
     * @param array $context
     * @return bool
     */
    protected function isValidAsset(Asset\LocalInterface $asset, array $context)
    {
        if ($asset->getContentType() != 'js'
            || $this->isExcluded($context['area'], $asset->getFilePath())
            || !$this->isAmd($asset)
        ) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Retunr bundle
     *
     * @param array $context
     * @return \Magento\Framework\App\View\Asset\Bundle|bool
     */
    protected function getBundle(array $context)
    {
        $bundlePath = $this->getBundlePath($context);

        if (isset($this->bundles[$bundlePath])) {
            $bundle =  $this->bundles[$bundlePath];
        } else {
            $bundle = $this->createBundle($context);
        }

        return $bundle;
    }

    /**
     * @param array $context
     * @return \Magento\Framework\App\View\Asset\Bundle
     */
    protected function createBundle(array $context)
    {
        $bundlePath = $this->getBundlePath($context);
        $bundle = $this->objectManager->create('Magento\Framework\App\View\Asset\Bundle');
        $bundle->setPath($bundlePath);
        $this->bundles[$bundlePath] = $bundle;
        return $bundle;
    }

    /**
     * Build bundle path
     *
     * @param array $context
     * @return string
     */
    protected function getBundlePath(array $context)
    {
        return $context['area'] . '/' . $context['theme'] . '/' . $context['locale'] . '/';
    }

    /**
     * @param Asset\LocalInterface $asset
     * @return bool
     */
    public static function isAmd(Asset\LocalInterface $asset)
    {
        return (bool)preg_match('/\Wdefine\s*\(/', $asset->getContent());
    }

    protected function prepareBundles()
    {
        foreach ($this->bundles as $bundle) {
            /** @var \Magento\Framework\App\View\Asset\Bundle $bundle */
            $bundle->fill();
            $bundle->prepare();
            $bundle->toJson();
            $bundle->wrapp();
        }
    }

    /**
     * Save bundle to js file
     *
     * @return bool
     */
    public function saveBundles()
    {
        $this->prepareBundles();

        $dir = $this->filesystem->getDirectoryWrite(DirectoryList::STATIC_VIEW);

        foreach ($this->bundles as $bundle) {
            /** @var \Magento\Framework\App\View\Asset\Bundle $bundle */
            foreach ($bundle->getContent() as $index => $part) {
                $dir->writeFile($bundle->getPath() . "bundle$index.js", $part);
            }
        }

        return true;
    }
}
