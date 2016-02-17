<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View;

use Zend\I18n\Translator\TranslatorAwareInterface;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\ConfigInterface;

/**
 * Plugin manager implementation for view helpers
 *
 * Enforces that helpers retrieved are instances of
 * Helper\HelperInterface. Additionally, it registers a number of default
 * helpers.
 */
class HelperPluginManager extends AbstractPluginManager
{
    /**
     * Default set of helpers factories
     *
     * @var array
     */
    protected $factories = array(
        'flashmessenger' => 'Zend\View\Helper\Service\FlashMessengerFactory',
        'identity'       => 'Zend\View\Helper\Service\IdentityFactory',
    );

    /**
     * Default set of helpers
     *
     * @var array
     */
    protected $invokableClasses = array(
        // basepath, doctype, and url are set up as factories in the ViewHelperManagerFactory.
        // basepath and url are not very useful without their factories, however the doctype
        // helper works fine as an invokable. The factory for doctype simply checks for the
        // config value from the merged config.
        'basepath'            => 'Zend\View\Helper\BasePath',
        'cycle'               => 'Zend\View\Helper\Cycle',
        'declarevars'         => 'Zend\View\Helper\DeclareVars',
        'doctype'             => 'Zend\View\Helper\Doctype', // overridden by a factory in ViewHelperManagerFactory
        'escapehtml'          => 'Zend\View\Helper\EscapeHtml',
        'escapehtmlattr'      => 'Zend\View\Helper\EscapeHtmlAttr',
        'escapejs'            => 'Zend\View\Helper\EscapeJs',
        'escapecss'           => 'Zend\View\Helper\EscapeCss',
        'escapeurl'           => 'Zend\View\Helper\EscapeUrl',
        'gravatar'            => 'Zend\View\Helper\Gravatar',
        'htmltag'             => 'Zend\View\Helper\HtmlTag',
        'headlink'            => 'Zend\View\Helper\HeadLink',
        'headmeta'            => 'Zend\View\Helper\HeadMeta',
        'headscript'          => 'Zend\View\Helper\HeadScript',
        'headstyle'           => 'Zend\View\Helper\HeadStyle',
        'headtitle'           => 'Zend\View\Helper\HeadTitle',
        'htmlflash'           => 'Zend\View\Helper\HtmlFlash',
        'htmllist'            => 'Zend\View\Helper\HtmlList',
        'htmlobject'          => 'Zend\View\Helper\HtmlObject',
        'htmlpage'            => 'Zend\View\Helper\HtmlPage',
        'htmlquicktime'       => 'Zend\View\Helper\HtmlQuicktime',
        'inlinescript'        => 'Zend\View\Helper\InlineScript',
        'json'                => 'Zend\View\Helper\Json',
        'layout'              => 'Zend\View\Helper\Layout',
        'paginationcontrol'   => 'Zend\View\Helper\PaginationControl',
        'partialloop'         => 'Zend\View\Helper\PartialLoop',
        'partial'             => 'Zend\View\Helper\Partial',
        'placeholder'         => 'Zend\View\Helper\Placeholder',
        'renderchildmodel'    => 'Zend\View\Helper\RenderChildModel',
        'rendertoplaceholder' => 'Zend\View\Helper\RenderToPlaceholder',
        'serverurl'           => 'Zend\View\Helper\ServerUrl',
        'url'                 => 'Zend\View\Helper\Url',
        'viewmodel'           => 'Zend\View\Helper\ViewModel',
    );

    /**
     * @var Renderer\RendererInterface
     */
    protected $renderer;

    /**
     * Constructor
     *
     * After invoking parent constructor, add an initializer to inject the
     * attached renderer and translator, if any, to the currently requested helper.
     *
     * @param null|ConfigInterface $configuration
     */
    public function __construct(ConfigInterface $configuration = null)
    {
        parent::__construct($configuration);

        $this->addInitializer(array($this, 'injectRenderer'))
             ->addInitializer(array($this, 'injectTranslator'));
    }

    /**
     * Set renderer
     *
     * @param  Renderer\RendererInterface $renderer
     * @return HelperPluginManager
     */
    public function setRenderer(Renderer\RendererInterface $renderer)
    {
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Retrieve renderer instance
     *
     * @return null|Renderer\RendererInterface
     */
    public function getRenderer()
    {
        return $this->renderer;
    }

    /**
     * Inject a helper instance with the registered renderer
     *
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectRenderer($helper)
    {
        $renderer = $this->getRenderer();
        if (null === $renderer) {
            return;
        }
        $helper->setView($renderer);
    }

    /**
     * Inject a helper instance with the registered translator
     *
     * @param  Helper\HelperInterface $helper
     * @return void
     */
    public function injectTranslator($helper)
    {
        if (!$helper instanceof TranslatorAwareInterface) {
            return;
        }

        $locator = $this->getServiceLocator();

        if (!$locator) {
            return;
        }

        if ($locator->has('MvcTranslator')) {
            $helper->setTranslator($locator->get('MvcTranslator'));
            return;
        }

        if ($locator->has('Zend\I18n\Translator\TranslatorInterface')) {
            $helper->setTranslator($locator->get('Zend\I18n\Translator\TranslatorInterface'));
            return;
        }

        if ($locator->has('Translator')) {
            $helper->setTranslator($locator->get('Translator'));
            return;
        }
    }

    /**
     * Validate the plugin
     *
     * Checks that the helper loaded is an instance of Helper\HelperInterface.
     *
     * @param  mixed                            $plugin
     * @return void
     * @throws Exception\InvalidHelperException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof Helper\HelperInterface) {
            // we're okay
            return;
        }

        throw new Exception\InvalidHelperException(sprintf(
            'Plugin of type %s is invalid; must implement %s\Helper\HelperInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin)),
            __NAMESPACE__
        ));
    }
}
