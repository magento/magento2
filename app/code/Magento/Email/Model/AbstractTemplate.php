<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Email\Model;

use Magento\Framework\App\TemplateTypesInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ScopeInterface;

/**
 * Template model class
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractTemplate extends AbstractModel implements TemplateTypesInterface
{
    /**
     * Default design area for emulation
     */
    const DEFAULT_DESIGN_AREA = 'frontend';

    /**
     * Email logo url
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO = 'design/email/logo';

    /**
     * Email logo alt text
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_ALT = 'design/email/logo_alt';

    /**
     * Email logo width
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_WIDTH      = 'design/email/logo_width';

    /**
     * Email logo height
     *
     * @var string
     */
    const XML_PATH_DESIGN_EMAIL_LOGO_HEIGHT     = 'design/email/logo_height';

    /**
     * Configuration of design package for template
     *
     * @var \Magento\Framework\Object
     */
    protected $_designConfig;

    /**
     * Whether template is child of another template
     *
     * @var bool
     */
    protected $_isChildTemplate = false;

    /**
     * Configuration of emulated design package.
     *
     * @var \Magento\Framework\Object|boolean
     */
    protected $_emulatedDesignConfig = false;

    /**
     * Package area
     *
     * @var string
     */
    protected $_area;

    /**
     * Store id
     *
     * @var int
     */
    protected $_store;

    /**
     * Design package instance
     *
     * @var \Magento\Framework\View\DesignInterface
     */
    protected $_design = null;

    /**
     * @var \Magento\Store\Model\App\Emulation
     */
    protected $_appEmulation;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_design = $design;
        $this->_area = isset($data['area']) ? $data['area'] : null;
        $this->_store = isset($data['store']) ? $data['store'] : null;
        $this->_appEmulation = $appEmulation;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, null, null, $data);
    }

    /**
     * Merge HTML and CSS and returns HTML that has CSS styles applied "inline" to the HTML tags. This is necessary
     * in order to support all email clients.
     *
     * @param $html
     * @return string
     */
    protected function _applyInlineCss($html)
    {
        // Check to see if the {{inlinecss file=""}} directive set CSS file(s) to inline
        $inlineCssFiles = $this->getInlineCssFiles();
        // Only run Emogrify if HTML exists and if there is at least one file to inline
        if ($html && !empty($inlineCssFiles)) {
            try {
                $cssToInline = $this->_getCssFilesContent($inlineCssFiles);
                $emogrifier = new \Pelago\Emogrifier();
                $emogrifier->setHtml($html);
                $emogrifier->setCss($cssToInline);
                // Don't parse inline <style> tags, since existing tag is intentionally for non-inline styles
                // TODO: Submit PR to Emogrifier to disable parsing inline styles: https://github.com/jjriv/emogrifier/issues/155
    //                $emogrifier->setParseInlineStyleTags(false);

                $processedHtml = $emogrifier->emogrify();
            } catch (Exception $e) {
                $processedHtml = '{CSS inlining error: ' . $e->getMessage() . '}' . PHP_EOL . $html;
            }
        } else {
            $processedHtml = $html;
        }
        return $processedHtml;
    }

    /**
     * Load CSS content from filesystem
     *
     * @param string $filename
     * @return string
     */
    protected function _getCssFilesContent($filenames)
    {
        // TODO: @Greg convert this code to trigger LESS compilation (if necessary) and load file using theme fallback mechanism
        $css = '';
        foreach ($filenames as $filename) {
            $file = BP . '/pub/static/frontend/Magento/blank/en_US/css/' . $filename;
            if (file_exists($file)) {
                $css .= file_get_contents($file);
            }
        }
        return $css;
        // OLD M1 Code
//        $storeId = $this->getDesignConfig()->getStore();
//        $area = $this->getDesignConfig()->getArea();
//        // This method should always be called within the context of the email's store, so these values will be correct
//        $package = Mage::getDesign()->getPackageName();
//        $theme = Mage::getDesign()->getTheme('skin');
//
//        $filePath = Mage::getDesign()->getFilename(
//            'css' . DS . $filename,
//            array(
//                '_type' => 'skin',
//                '_default' => false,
//                '_store' => $storeId,
//                '_area' => $area,
//                '_package' => $package,
//                '_theme' => $theme,
//            )
//        );
//
//        if (is_readable($filePath)) {
//            return (string) file_get_contents($filePath);
//        }
//
//        // If file can't be found, return empty string
//        return '';
    }

    // TODO: Determine if we're going to keep this method
    /**
     * Accepts a path to a System Config setting that contains a comma-delimited list of files to load. Loads those
     * files and then returns the concatenated content.
     *
     * @param $configPath
     * @return string
     */
    protected function _getCssByConfig($configPath)
    {
        // TODO: @Greg convert this code to trigger LESS compilation (if necessary) and load file using theme fallback mechanism
        $file = BP . '/pub/static/frontend/Magento/blank/en_US/css/email-non-inline.css';
        if (file_exists($file)) {
            return file_get_contents($file);
        }

        // OLD M1 Code
//        if (!isset($this->_cssFileCache[$configPath])) {
//            $filesToLoad = Mage::getStoreConfig($configPath);
//            if (!$filesToLoad) {
//                return '';
//            }
//            $files = array_map('trim', explode(",", $filesToLoad));
//
//            $css = '';
//            foreach($files as $fileName) {
//                $css .= $this->_getCssFileContent($fileName) . "\n";
//            }
//            $this->_cssFileCache[$configPath] = $css;
//        }
//
//        return $this->_cssFileCache[$configPath];
    }

    /**
     * Loads content of files with non-inline CSS styles and merges them with any CSS styles that are specified
     * within the <!--@styles @--> comments or in the Transactional Emails
     *
     * @return string
     */
    protected function _getNonInlineCssTag()
    {
        $styleTagWrapper = "<style type=\"text/css\">\n%s\n</style>\n";
        // Load the non-inline CSS styles from theme so they can be included in the style tag
        // TODO: Refactor
        $styleTagContent = $this->_getCssByConfig('TODO: REFACTOR THIS CODE');
        // Load the CSS that is included in the <!--@styles @--> comment or is added via Transactional Emails in admin
        $styleTagContent .= $this->getTemplateStyles();
        return sprintf($styleTagWrapper, $styleTagContent);
    }

    /**
     * Return logo URL for emails. Take logo from theme if custom logo is undefined
     *
     * @param  \Magento\Store\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoUrl($store)
    {
        $store = $this->_storeManager->getStore($store);
        $fileName = $this->_scopeConfig->getValue(
            self::XML_PATH_DESIGN_EMAIL_LOGO,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($fileName) {
            $uploadDir = \Magento\Config\Model\Config\Backend\Email\Logo::UPLOAD_DIR;
            $mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
            if ($mediaDirectory->isFile($uploadDir . '/' . $fileName)) {
                return $this->_storeManager->getStore()->getBaseUrl(
                    \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
                ) . $uploadDir . '/' . $fileName;
            }
        }
        return $this->getDefaultEmailLogo();
    }

    /**
     * Return logo alt for emails
     *
     * @param  \Magento\Store\Model\Store|int|string $store
     * @return string
     */
    protected function _getLogoAlt($store)
    {
        $store = $this->_storeManager->getStore($store);
        $alt = $this->_scopeConfig->getValue(
            self::XML_PATH_DESIGN_EMAIL_LOGO_ALT,
            ScopeInterface::SCOPE_STORE,
            $store
        );
        if ($alt) {
            return $alt;
        }
        return $store->getFrontendName();
    }

    /**
     * Add variables that are used by transactional and newsletter emails
     *
     * @param $variables
     * @param $storeId
     * @return mixed
     */
    protected function _addEmailVariables($variables, $storeId)
    {
        $store = $this->_storeManager->getStore($storeId);
        if (!isset($variables['store'])) {
            $variables['store'] = $store;
        }
        if (!isset($variables['logo_url'])) {
            $variables['logo_url'] = $this->_getLogoUrl($storeId);
        }
        if (!isset($variables['logo_alt'])) {
            $variables['logo_alt'] = $this->_getLogoAlt($storeId);
        }
        if (!isset($variables['logo_width'])) {
            $variables['logo_width'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DESIGN_EMAIL_LOGO_WIDTH,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['logo_height'])) {
            $variables['logo_height'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DESIGN_EMAIL_LOGO_HEIGHT,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_phone'])) {
            $variables['store_phone'] = $this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_STORE_PHONE,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_hours'])) {
            $variables['store_hours'] = $this->_scopeConfig->getValue(
                \Magento\Store\Model\Store::XML_PATH_STORE_STORE_HOURS,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        if (!isset($variables['store_email'])) {
            $variables['store_email'] = $this->_scopeConfig->getValue(
                // TODO: @Erik replace this with constant. Need to create constant.
                'trans_email/ident_support/email',
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
        // If template is text mode, don't include styles
        if (!$this->isPlain() && !isset($variables['non_inline_styles'])) {
            $variables['non_inline_styles'] = $this->_getNonInlineCssTag();
        }

        return $variables;
    }

    /**
     * Applying of design config
     *
     * @return $this
     */
    protected function _applyDesignConfig()
    {
        $designConfig = $this->getDesignConfig();
        $store = $designConfig->getStore();
        $storeId = is_object($store) ? $store->getId() : $store;
        $area = $designConfig->getArea();
        if ($storeId !== null) {
            $this->_appEmulation->startEnvironmentEmulation($storeId, $area);
        }
        return $this;
    }

    /**
     * Revert design settings to previous
     *
     * @return $this
     */
    protected function _cancelDesignConfig()
    {
        $this->_appEmulation->stopEnvironmentEmulation();
        return $this;
    }

    /**
     * Get design configuration data
     *
     * @return \Magento\Framework\Object
     */
    public function getDesignConfig()
    {
        if ($this->_designConfig === null) {
            if ($this->_area === null) {
                $this->_area = $this->_design->getArea();
            }
            if ($this->_store === null) {
                $this->_store = $this->_storeManager->getStore()->getId();
            }
            $this->_designConfig = new \Magento\Framework\Object(
                ['area' => $this->_area, 'store' => $this->_store]
            );
        }
        return $this->_designConfig;
    }

    /**
     * Initialize design information for template processing
     *
     * @param array $config
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setDesignConfig(array $config)
    {
        if (!isset($config['area']) || !isset($config['store'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Design config must have area and store.'));
        }
        $this->getDesignConfig()->setData($config);
        return $this;
    }

    /**
     * Gets whether template is child of another template
     *
     * @return bool
     */
    public function getIsChildTemplate()
    {
        return $this->_isChildTemplate;
    }

    /**
     * Sets whether template is child of another template
     *
     * @param bool $isChildTemplate
     * @return $this
     */
    public function setIsChildTemplate($isChildTemplate)
    {
        $this->_isChildTemplate = $isChildTemplate;
        return $this;
    }

    /**
     * Save current design config and replace with design config from specified store
     * Event is not dispatched.
     *
     * @param int|string $storeId
     * @param string $area
     * @return void
     */
    public function emulateDesign($storeId, $area = self::DEFAULT_DESIGN_AREA)
    {
        if ($storeId) {
            // save current design settings
            $this->_emulatedDesignConfig = clone $this->getDesignConfig();
            if ($this->getDesignConfig()->getStore() != $storeId) {
                $this->setDesignConfig(['area' => $area, 'store' => $storeId]);
                $this->_applyDesignConfig();
            }
        } else {
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Revert to last design config, used before emulation
     *
     * @return void
     */
    public function revertDesign()
    {
        if ($this->_emulatedDesignConfig) {
            $this->setDesignConfig($this->_emulatedDesignConfig->getData());
            $this->_cancelDesignConfig();
            $this->_emulatedDesignConfig = false;
        }
    }

    /**
     * Return true if template type eq text
     *
     * @return boolean
     */
    public function isPlain()
    {
        return $this->getType() == self::TYPE_TEXT;
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    abstract public function getType();
}
