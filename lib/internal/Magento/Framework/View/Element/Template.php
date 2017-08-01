<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;

/**
 * Standard Magento block.
 * Should be used when you declare a block in frontend area layout handle.
 *
 * Avoid extending this class.
 *
 * If you need custom presentation logic in your blocks, use this class as block, and declare
 * custom view models in block arguments in layout handle file.
 *
 * Example:
 * <block name="my.block" class="Magento\Backend\Block\Template" template="My_Module::template.phtml" >
 *      <arguments>
 *          <argument name="viewModel" xsi:type="object">My\Module\ViewModel\Custom</argument>
 *      </arguments>
 * </block>
 *
 * @api
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 2.0.0
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
     * @since 2.0.0
     */
    protected $_viewVars = [];

    /**
     * Base URL
     *
     * @var string
     * @since 2.0.0
     */
    protected $_baseUrl;

    /**
     * JS URL
     *
     * @var string
     * @since 2.0.0
     */
    protected $_jsUrl;

    /**
     * Is allowed symlinks flag
     *
     * @var bool
     * @since 2.0.0
     */
    protected $_allowSymlinks;

    /**
     * Filesystem instance
     *
     * @var Filesystem
     * @since 2.0.0
     */
    protected $_filesystem;

    /**
     * Path to template file in theme.
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template;

    /**
     * Template engine pool
     *
     * @var \Magento\Framework\View\TemplateEnginePool
     * @since 2.0.0
     */
    protected $templateEnginePool;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * Application state
     *
     * @var \Magento\Framework\App\State
     * @since 2.0.0
     */
    protected $_appState;

    /**
     * Root directory instance
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected $directory;

    /**
     * Media directory instance
     *
     * @var \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    private $mediaDirectory;

    /**
     * Template context
     *
     * @var \Magento\Framework\View\Element\BlockInterface
     * @since 2.0.0
     */
    protected $templateContext;

    /**
     * @var \Magento\Framework\View\Page\Config
     * @since 2.0.0
     */
    protected $pageConfig;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Resolver
     * @since 2.0.0
     */
    protected $resolver;

    /**
     * @var \Magento\Framework\View\Element\Template\File\Validator
     * @since 2.0.0
     */
    protected $validator;

    /**
     * Constructor
     *
     * @param Template\Context $context
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(Template\Context $context, array $data = [])
    {
        $this->validator = $context->getValidator();
        $this->resolver = $context->getResolver();
        $this->_filesystem = $context->getFilesystem();
        $this->templateEnginePool = $context->getEnginePool();
        $this->_storeManager = $context->getStoreManager();
        $this->_appState = $context->getAppState();
        $this->templateContext = $this;
        $this->pageConfig = $context->getPageConfig();
        parent::__construct($context, $data);
    }

    /**
     * Set template context. Sets the object that should represent $block in template
     *
     * @param \Magento\Framework\View\Element\BlockInterface $templateContext
     * @return void
     * @since 2.0.0
     */
    public function setTemplateContext($templateContext)
    {
        $this->templateContext = $templateContext;
    }

    /**
     * Internal constructor, that is called from real constructor
     * @return void
     * @since 2.0.0
     */
    protected function _construct()
    {
        parent::_construct();

        /*
         * In case template was passed through constructor
         * we assign it to block's property _template
         * Mainly for those cases when block created
         * not via \Magento\Framework\View\Model\LayoutInterface::addBlock()
         */
        if ($this->hasData('template')) {
            $this->setTemplate($this->getData('template'));
        }
    }

    /**
     * Get relevant path to template
     *
     * @return string
     * @since 2.0.0
     */
    public function getTemplate()
    {
        return $this->_template;
    }

    /**
     * Set path to template used for generating block's output.
     *
     * @param string $template
     * @return $this
     * @since 2.0.0
     */
    public function setTemplate($template)
    {
        $this->_template = $template;
        return $this;
    }

    /**
     * Get absolute path to template
     *
     * @param string|null $template
     * @return string|bool
     * @since 2.0.0
     */
    public function getTemplateFile($template = null)
    {
        $params = ['module' => $this->getModuleName()];
        $area = $this->getArea();
        if ($area) {
            $params['area'] = $area;
        }
        return $this->resolver->getTemplateFileName($template ?: $this->getTemplate(), $params);
    }

    /**
     * Get design area
     *
     * @return string
     * @since 2.0.0
     */
    public function getArea()
    {
        return $this->_getData('area') ? $this->_getData('area') : $this->_appState->getAreaCode();
    }

    /**
     * Assign variable
     *
     * @param   string|array $key
     * @param   mixed $value
     * @return  $this
     * @since 2.0.0
     */
    public function assign($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $subKey => $subValue) {
                $this->assign($subKey, $subValue);
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
     * @since 2.0.0
     */
    public function fetchView($fileName)
    {
        $relativeFilePath = $this->getRootDirectory()->getRelativePath($fileName);
        \Magento\Framework\Profiler::start(
            'TEMPLATE:' . $fileName,
            ['group' => 'TEMPLATE', 'file_name' => $relativeFilePath]
        );

        if ($this->validator->isValid($fileName)) {
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $templateEngine = $this->templateEnginePool->get($extension);
            $html = $templateEngine->render($this->templateContext, $fileName, $this->_viewVars);
        } else {
            $html = '';
            $templatePath = $fileName ?: $this->getTemplate();
            $errorMessage = "Invalid template file: '{$templatePath}' in module: '{$this->getModuleName()}'"
                . " block's name: '{$this->getNameInLayout()}'";
            if ($this->_appState->getMode() === \Magento\Framework\App\State::MODE_DEVELOPER) {
                throw new \Magento\Framework\Exception\ValidatorException(
                    new \Magento\Framework\Phrase(
                        $errorMessage
                    )
                );
            }
            $this->_logger->critical($errorMessage);
        }

        \Magento\Framework\Profiler::stop('TEMPLATE:' . $fileName);
        return $html;
    }

    /**
     * Render block HTML
     *
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
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
     * @param \Magento\Framework\DataObject $object
     * @param string $key
     * @return mixed
     * @since 2.0.0
     */
    public function getObjectData(\Magento\Framework\DataObject $object, $key)
    {
        return $object->getDataUsingMethod((string)$key);
    }

    /**
     * Get cache key informative items
     *
     * @return array
     * @since 2.0.0
     */
    public function getCacheKeyInfo()
    {
        return [
            'BLOCK_TPL',
            $this->_storeManager->getStore()->getCode(),
            $this->getTemplateFile(),
            'base_url' => $this->getBaseUrl(),
            'template' => $this->getTemplate()
        ];
    }

    /**
     * Instantiates filesystem directory
     *
     * @return \Magento\Framework\Filesystem\Directory\ReadInterface
     * @since 2.0.0
     */
    protected function getRootDirectory()
    {
        if (null === $this->directory) {
            $this->directory = $this->_filesystem->getDirectoryRead(DirectoryList::ROOT);
        }

        return $this->directory;
    }

    /**
     * Get media directory
     *
     * @return \Magento\Framework\Filesystem\Directory\Read
     * @since 2.0.0
     */
    protected function getMediaDirectory()
    {
        if (!$this->mediaDirectory) {
            $this->mediaDirectory = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
        }
        return $this->mediaDirectory;
    }
}
