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
 * @category    Magento
 * @package     Magento_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Base html block
 *
 * @category   Magento
 * @package    Magento_Core
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Core\Block;

class Template extends \Magento\Core\Block\AbstractBlock
{
    const XML_PATH_TEMPLATE_ALLOW_SYMLINK       = 'dev/template/allow_symlink';

    /**
     * Assigned variables for view
     *
     * @var array
     */
    protected $_viewVars = array();

    protected $_baseUrl;

    protected $_jsUrl;

    /**
     * Is allowed symlinks flag
     *
     * @var bool
     */
    protected $_allowSymlinks = null;

    /**
     * @var \Magento\App\Dir
     */
    protected $_dirs;

    /**
     * @var \Magento\Core\Model\Logger
     */
    protected $_logger;

    /**
     * @var \Magento\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Core\Model\View\FileSystem
     */
    protected $_viewFileSystem;

    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template;

    /**
     * @var \Magento\Core\Model\TemplateEngine\Pool
     */
    protected $_templateEnginePool;

    /**
     * Core data
     *
     * @var \Magento\Core\Helper\Data
     */
    protected $_coreData = null;

    /**
     * @var \Magento\Core\Model\App
     */
    protected $_storeManager;

    /**
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param array $data
     * @todo Remove injection of the core helper from this class and its descendants, because it's no longer used
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        array $data = array()
    ) {
        $this->_coreData = $coreData;
        $this->_dirs = $context->getDirs();
        $this->_logger = $context->getLogger();
        $this->_filesystem = $context->getFilesystem();
        $this->_viewFileSystem = $context->getViewFileSystem();
        $this->_templateEnginePool = $context->getEnginePool();
        $this->_storeManager = $context->getApp();
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
         * not via \Magento\Core\Model\Layout::addBlock()
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
     * @return \Magento\Core\Block\Template
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
     * @return string
     */
    public function getArea()
    {
        $result = $this->_getData('area');
        if (!$result && $this->getLayout()) {
            $result = $this->getLayout()->getArea();
        }
        return $result;
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  \Magento\Core\Block\Template
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
        $viewShortPath = str_replace($this->_dirs->getDir(\Magento\App\Dir::ROOT), '', $fileName);
        \Magento\Profiler::start('TEMPLATE:' . $fileName, array('group' => 'TEMPLATE', 'file_name' => $viewShortPath));

        if (($this->_filesystem->isPathInDirectory($fileName, $this->_dirs->getDir(\Magento\App\Dir::APP))
                || $this->_filesystem->isPathInDirectory($fileName, $this->_dirs->getDir(\Magento\App\Dir::THEMES))
                || $this->_getAllowSymlinks()) && $this->_filesystem->isFile($fileName)
        ) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $templateEngine = $this->_templateEnginePool->get($extension);
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
    protected function _getAllowSymlinks()
    {
        if (is_null($this->_allowSymlinks)) {
            $this->_allowSymlinks = $this->_storeConfig->getConfigFlag(self::XML_PATH_TEMPLATE_ALLOW_SYMLINK);
        }
        return $this->_allowSymlinks;
    }
}
