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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View\Element;

use Magento\Filesystem;

/**
 * Base html block
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Template extends AbstractBlock
{
    /**
     * Config path to 'Allow Symlinks' template settings
     */
    const XML_PATH_TEMPLATE_ALLOW_SYMLINK = 'dev/template/allow_symlink';

    /**
     * Assigned variables for view
     *
     * @var array
     */
    protected $_viewVars = array();

    /**
     * @var string
     */
    protected $_baseUrl;

    /**
     * @var string
     */
    protected $_jsUrl;

    /**
     * Is allowed symlinks flag
     *
     * @var bool
     */
    protected $_allowSymlinks;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template;

    /**
     * @var \Magento\View\TemplateEnginePool
     */
    protected $templateEnginePool;

    /**
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\App\State
     */
    protected $_appState;

    /**
     * Root directory instance
     *
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $directory;

    /**
     * Media directory instance
     *
     * @var \Magento\Filesystem\Directory\ReadInterface
     */
    protected $mediaDirectory;

    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        array $data = array()
    ) {
        $this->_filesystem = $context->getFilesystem();
        $this->_viewFileSystem = $context->getViewFileSystem();
        $this->templateEnginePool = $context->getEnginePool();
        $this->_storeManager = $context->getStoreManager();
        $this->_appState = $context->getAppState();
        parent::__construct($context, $data);
    }

    /**
     * Internal constructor, that is called from real constructor
     */
    protected function _construct()
    {
        parent::_construct();

        /*
         * In case template was passed through constructor
         * we assign it to block's property _template
         * Mainly for those cases when block created
         * not via \Magento\View\Model\LayoutInterface::addBlock()
         */
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * Get relevant path to template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Set path to template used for generating block's output.
     *
     * @param string $template
     * @return \Magento\View\Element\Template
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Get absolute path to template
     *
     * @return string
     */
    public function getTemplateFile()
    {
        $params = array('module' => $this->getModuleName());
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }
        $templateName = $this->_viewFileSystem->getFilename($this->getTemplate(), $params);
        return $templateName;
    }

    /**
     * Get design area
     *
     * @return string
     */
    public function getArea()
    {
        return $this->_appState->getAreaCode();
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  \Magento\View\Element\Template
     */
    public function assign($key, $value=null)
    {
        if (is_array($key)) {
            foreach ($key as $k=>$v) {
                $this->assign($k, $v);
            }
        } else {
            $this->_viewVars[$key] = $value;
        }
        return $this;
    }

    /**
     * Retrieve block view from file (template)
     *
     * @param string $fileName
     * @return string
     */
    public function fetchView($fileName)
    {
        $relativeFilePath = $this->getRootDirectory()->getRelativePath($fileName);
        \Magento\Profiler::start(
            'TEMPLATE:' . $fileName, array('group' => 'TEMPLATE', 'file_name' => $relativeFilePath)
        );

        if ($this->isTemplateFileValid($fileName)) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $templateEngine = $this->templateEnginePool->get($extension);
            $html = $templateEngine->render($this, $fileName, $this->_viewVars);
        } else {
            $html = '';
            $this->_logger->log("Invalid template file: '{$fileName}'", \Zend_Log::CRIT);
        }

        \Magento\Profiler::stop('TEMPLATE:' . $fileName);
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->getTemplate()) {
            return '';
        }
        return $this->fetchView($this->getTemplateFile());
    }

    /**
     * Get base url of the application
     *
     * @return string
     */
    public function getBaseUrl()
    {
        if (!$this->_baseUrl) {
            $this->_baseUrl = $this->_urlBuilder->getBaseUrl();
        }
        return $this->_baseUrl;
    }

    /**
     * Get data from specified object
     *
     * @param \Magento\Object $object
     * @param string $key
     * @return mixed
     */
    public function getObjectData(\Magento\Object $object, $key)
    {
        return $object->getDataUsingMethod((string)$key);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return array(
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            $this->getTemplateFile(),
            'template' => $this->getTemplate()
        );
    }

    /**
     * Get is allowed symliks flag
     *
     * @return bool
     */
    protected function isAllowSymlinks()
    {
        if (null === $this->_allowSymlinks) {
            $this->_allowSymlinks = $this->_storeConfig->getConfigFlag(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK);
        }
        return $this->_allowSymlinks;
    }

    /**
     * Instantiates filesystem directory
     *
     * @return \Magento\Filesystem\Directory\ReadInterface
     */
    protected function getRootDirectory()
    {
        if (null === $this->directory) {
            $this->directory = $this->_filesystem->getDirectoryRead(Filesystem::ROOT);
        }

        return $this->directory;
    }

    /**
     * Get media directory
     *
     * @return \Magento\Filesystem\Directory\Read
     */
    protected function getMediaDirectory()
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->_filesystem->getDirectoryRead(Filesystem::MEDIA);
        }
        return $this->mediaDirectory;
    }

    /**
     * Checks whether the provided file can be rendered.
     *
     * Available directories which are allowed to be rendered
     * (the template file should be located under these directories):
     *  - app
     *  - design
     *
     * @param string $fileName
     * @return bool
     */
    protected function isTemplateFileValid($fileName)
    {
        $fileName = str_replace('\\', '/', $fileName);

        $themesDir = str_replace('\\', '/', $this->_filesystem->getPath(Filesystem::THEMES));
        $appDir = str_replace('\\', '/', $this->_filesystem->getPath(Filesystem::APP));
        return (
            $this->isPathInDirectory($fileName, $appDir)
            || $this->isPathInDirectory($fileName, $themesDir)
            || $this->isAllowSymlinks()
        ) && $this->getRootDirectory()->isFile($this->getRootDirectory()->getRelativePath($fileName));
    }

    /**
     * Checks whether path related to the directory
     *
     * @param string $path
     * @param string $directory
     * @return bool
     */
    protected function isPathInDirectory($path, $directory)
    {
        return 0 === strpos($path, $directory);
    }
}
