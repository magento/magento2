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
namespace Magento\Rss\Block;

/**
 * Class Feeds
 * @package Magento\Rss\Block
 */
class Feeds extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'feeds.phtml';

    /**
     * @var \Magento\Framework\App\Rss\RssManagerInterface
     */
    protected $rssManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Rss\RssManagerInterface $rssManager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Rss\RssManagerInterface $rssManager,
        array $data = array()
    ) {
        $this->rssManager = $rssManager;
        parent::__construct($context, $data);
    }

    /**
     * Add Link elements to head
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $head = $this->getLayout()->getBlock('head');
        $feeds = $this->getFeeds();
        if ($head && !empty($feeds)) {
            foreach ($feeds as $feed) {
                if (!isset($feed['group'])) {
                    $head->addRss($feed['label'], $feed['link']);
                } else {
                    foreach ($feed['feeds'] as $item) {
                        $head->addRss($item['label'], $item['link']);
                    }
                }
            }
        }
        return parent::_prepareLayout();
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $providers = $this->rssManager->getProviders();
        $feeds = array();
        $groups = array();
        foreach ($providers as $provider) {
            $item = $provider->getFeeds();
            if (empty($item)) {
                continue;
            }

            if (isset($item['group'])) {
                $groups[] = $item;
            } else {
                $feeds[] = $item;
            }
        }
        $feeds = array_merge($feeds, $groups);

        return $feeds;
    }
}
