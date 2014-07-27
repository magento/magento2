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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\PageCache\Model;

/**
 * Class Observer
 */
class Observer
{
    /**
     * Application config object
     *
     * @var \Magento\PageCache\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Framework\App\PageCache\Cache
     */
    protected $_cache;

    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\Cache\TypeListInterface
     */
    protected $_typeList;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $_escaper;
    
    /**
     * @var \Magento\Framework\App\PageCache\FormKey
     */
    protected $_formKey;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\Framework\App\PageCache\Cache $cache
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\Framework\App\Cache\TypeListInterface $typeList
     * @param \Magento\Framework\Session\Generic $session
     * @param \Magento\Framework\App\PageCache\FormKey $formKey
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\Framework\App\PageCache\Cache $cache,
        \Magento\PageCache\Helper\Data $helper,
        \Magento\Framework\App\Cache\TypeListInterface $typeList,
        \Magento\Framework\App\PageCache\FormKey $formKey,
        \Magento\Framework\Session\Generic $session,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_helper = $helper;
        $this->_typeList = $typeList;
        $this->_session = $session;
        $this->_formKey = $formKey;
        $this->_escaper = $escaper;
    }

    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function processLayoutRenderElement(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Framework\View\Layout $layout */
        $layout = $event->getLayout();
        if ($layout->isCacheable() && $this->_config->isEnabled()) {
            $name = $event->getElementName();
            $block = $layout->getBlock($name);
            $transport = $event->getTransport();
            if ($block instanceof \Magento\Framework\View\Element\AbstractBlock) {
                $blockTtl = $block->getTtl();
                $varnishIsEnabledFlag = ($this->_config->getType() == \Magento\PageCache\Model\Config::VARNISH);
                $output = $transport->getData('output');
                if ($varnishIsEnabledFlag && isset($blockTtl)) {
                    $output = $this->_wrapEsi($block);
                } elseif ($block->isScopePrivate()) {
                    $output = sprintf(
                        '<!-- BLOCK %1$s -->%2$s<!-- /BLOCK %1$s -->',
                        $block->getNameInLayout(),
                        $output
                    );
                }
                $transport->setData('output', $output);
            }
        }
    }

    /**
     * Replace the output of the block, containing ttl attribute, with ESI tag
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $block
     * @return string
     */
    protected function _wrapEsi(\Magento\Framework\View\Element\AbstractBlock $block)
    {
        $url = $block->getUrl(
            'page_cache/block/esi',
            array(
                'blocks' => json_encode(array($block->getNameInLayout())),
                'handles' => json_encode($this->_helper->getActualHandles())
            )
        );
        return sprintf('<esi:include src="%s" />', $url);
    }

    /**
     * If Built-In caching is enabled it collects array of tags
     * of incoming object and asks to clean cache.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function flushCacheByTags(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Framework\Object\IdentityInterface) {
                $tags = $object->getIdentities();
                foreach ($tags as $tag) {
                    $tags[] = preg_replace("~_\\d+$~", '', $tag);
                }
                $this->_cache->clean(array_unique($tags));
            }
        }
    }

    /**
     * Flash Built-In cache
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function flushAllCache(\Magento\Framework\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN) {
            $this->_cache->clean();
        }
    }

    /**
     * Invalidate full page cache
     *
     * @return \Magento\PageCache\Model\Observer
     */
    public function invalidateCache()
    {
        if ($this->_config->isEnabled()) {
            $this->_typeList->invalidate('full_page');
        }
        return $this;
    }

    /**
     * Register form key in session from cookie value
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function registerFormKeyFromCookie(\Magento\Framework\Event\Observer $observer)
    {
        $formKeyFromCookie = $this->_formKey->get();
        if ($formKeyFromCookie) {
            $this->_session->setData(
                \Magento\Framework\Data\Form\FormKey::FORM_KEY,
                $this->_escaper->escapeHtml($formKeyFromCookie)
            );
        }
    }
}
