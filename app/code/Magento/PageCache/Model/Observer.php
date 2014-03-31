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
 * @package     Magento_PageCache
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
     * @var \Magento\App\PageCache\Cache
     */
    protected $_cache;

    /**
     * @var \Magento\PageCache\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\App\Cache\TypeListInterface
     */
    protected $_typeList;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_session;

    /**
     * @var \Magento\Core\Model\Session
     */
    protected $_formKey;

    /**
     * Constructor
     *
     * @param Config $config
     * @param \Magento\App\PageCache\Cache $cache
     * @param \Magento\PageCache\Helper\Data $helper
     * @param \Magento\App\Cache\TypeListInterface $typeList
     * @param \Magento\Core\Model\Session $session
     * @param \Magento\App\PageCache\FormKey $formKey
     */
    public function __construct(
        \Magento\PageCache\Model\Config $config,
        \Magento\App\PageCache\Cache $cache,
        \Magento\PageCache\Helper\Data $helper,
        \Magento\App\Cache\TypeListInterface $typeList,
        \Magento\App\PageCache\FormKey $formKey,
        \Magento\Core\Model\Session $session
    ) {
        $this->_config = $config;
        $this->_cache = $cache;
        $this->_helper = $helper;
        $this->_typeList = $typeList;
        $this->_session = $session;
        $this->_formKey = $formKey;
    }

    /**
     * Add comment cache containers to private blocks
     * Blocks are wrapped only if page is cacheable
     *
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function processLayoutRenderElement(\Magento\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        /** @var \Magento\Core\Model\Layout $layout */
        $layout = $event->getLayout();
        if ($layout->isCacheable() && $this->_config->isEnabled()) {
            $name = $event->getElementName();
            $block = $layout->getBlock($name);
            $transport = $event->getTransport();
            if ($block instanceof \Magento\View\Element\AbstractBlock) {
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
     * @param \Magento\View\Element\AbstractBlock $block
     * @return string
     */
    protected function _wrapEsi(\Magento\View\Element\AbstractBlock $block)
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
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function flushCacheByTags(\Magento\Event\Observer $observer)
    {
        if ($this->_config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->_config->isEnabled()) {
            $object = $observer->getEvent()->getObject();
            if ($object instanceof \Magento\Object\IdentityInterface) {
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
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function flushAllCache(\Magento\Event\Observer $observer)
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
     * @param \Magento\Event\Observer $observer
     * @return void
     */
    public function registerFormKeyFromCookie(\Magento\Event\Observer $observer)
    {
        if (!$this->_config->isEnabled()) {
            return;
        }

        $formKeyFromCookie = $this->_formKey->get();
        if ($formKeyFromCookie) {
            $this->_session->setData(\Magento\Data\Form\FormKey::FORM_KEY, $formKeyFromCookie);
        }
    }
}
