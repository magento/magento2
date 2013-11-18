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
 * @package     Magento_Adminhtml
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Sitemap grid link column renderer
 *
 * @category   Magento
 * @package    Magento_Sitemap
 */
namespace Magento\Adminhtml\Block\Sitemap\Grid\Renderer;

class Link extends \Magento\Adminhtml\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var \Magento\Filesystem $filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Sitemap\Model\SitemapFactory
     */
    protected $_sitemapFactory;

    /**
     * @param \Magento\Sitemap\Model\SitemapFactory $sitemapFactory
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Filesystem $filesystem
     * @param array $data
     */
    public function __construct(
        \Magento\Sitemap\Model\SitemapFactory $sitemapFactory,
        \Magento\Backend\Block\Context $context,
        \Magento\Filesystem $filesystem,
        array $data = array()
    ) {
        $this->_sitemapFactory = $sitemapFactory;
        $this->_filesystem = $filesystem;
        parent::__construct($context, $data);
    }

    /**
     * Prepare link to display in grid
     *
     * @param \Magento\Object $row
     * @return string
     */
    public function render(\Magento\Object $row)
    {
        /** @var $sitemap \Magento\Sitemap\Model\Sitemap */
        $sitemap = $this->_sitemapFactory->create();
        $url = $this->escapeHtml($sitemap->getSitemapUrl($row->getSitemapPath(), $row->getSitemapFilename()));

        $fileName = preg_replace('/^\//', '', $row->getSitemapPath() . $row->getSitemapFilename());
        if ($this->_filesystem->isFile(BP . DS . $fileName)) {
            return sprintf('<a href="%1$s">%1$s</a>', $url);
        }

        return $url;
    }

}
