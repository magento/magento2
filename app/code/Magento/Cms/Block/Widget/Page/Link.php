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
 * @package     Magento_Cms
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Widget to display link to CMS page
 *
 * @category   Magento
 * @package    Magento_Cms
 * @author     Magento Core Team <core@magentocommerce.com>
 */

namespace Magento\Cms\Block\Widget\Page;

class Link
    extends \Magento\Core\Block\Html\Link
    implements \Magento\Widget\Block\BlockInterface
{
    /**
     * Prepared href attribute
     *
     * @var string
     */
    protected $_href;

    /**
     * Prepared title attribute
     *
     * @var string
     */
    protected $_title;

    /**
     * Prepared anchor text
     *
     * @var string
     */
    protected $_anchorText;

    /**
     * @var \Magento\Cms\Model\Resource\Page
     */
    protected $_resourcePage;

    /**
     * Cms page
     *
     * @var \Magento\Cms\Helper\Page
     */
    protected $_cmsPage;

    /**
     * Store manager
     *
     * @var \Magento\Core\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Construct
     *
     * @param \Magento\Core\Helper\Data $coreData
     * @param \Magento\Core\Block\Template\Context $context
     * @param \Magento\Cms\Model\Resource\Page $resourcePage
     * @param \Magento\Cms\Helper\Page $cmsPage
     * @param \Magento\Core\Model\StoreManagerInterface $storeManager
     * @param array $data
     */
    public function __construct(
        \Magento\Core\Helper\Data $coreData,
        \Magento\Core\Block\Template\Context $context,
        \Magento\Cms\Model\Resource\Page $resourcePage,
        \Magento\Cms\Helper\Page $cmsPage,
        \Magento\Core\Model\StoreManagerInterface $storeManager,
        array $data = array()
    ) {
        parent::__construct($coreData, $context, $data);
        $this->_resourcePage = $resourcePage;
        $this->_cmsPage = $cmsPage;
        $this->_storeManager = $storeManager;
    }

    /**
     * Prepare page url. Use passed identifier
     * or retrieve such using passed page id.
     *
     * @return string
     */
    public function getHref()
    {
        if (!$this->_href) {
            $this->_href = '';
            if ($this->getData('href')) {
                $this->_href = $this->getData('href');
            } else if ($this->getData('page_id')) {
                $this->_href = $this->_cmsPage->getPageUrl($this->getData('page_id'));
            }
        }

        return $this->_href;
    }

    /**
     * Prepare anchor title attribute using passed title
     * as parameter or retrieve page title from DB using passed identifier or page id.
     *
     * @return string
     */
    public function getTitle()
    {
        if (!$this->_title) {
            $this->_title = '';
            if ($this->getData('title') !== null) {
                // compare to null used here bc user can specify blank title
                $this->_title = $this->getData('title');
            } else if ($this->getData('page_id')) {
                $this->_title = $this->_resourcePage->getCmsPageTitleById($this->getData('page_id'));
            } else if ($this->getData('href')) {
                $this->_title = $this->_resourcePage->setStore($this->_storeManager->getStore())
                    ->getCmsPageTitleByIdentifier($this->getData('href'));
            }
        }

        return $this->_title;
    }

    /**
     * Prepare anchor text using passed text as parameter.
     * If anchor text was not specified use title instead and
     * if title will be blank string, page identifier will be used.
     *
     * @return string
     */
    public function getAnchorText()
    {
        if ($this->getData('anchor_text')) {
            $this->_anchorText = $this->getData('anchor_text');
        } else if ($this->getTitle()) {
            $this->_anchorText = $this->getTitle();
        } else if ($this->getData('href')) {
            $this->_anchorText = $this->_resourcePage->setStore($this->_storeManager->getStore())
                ->getCmsPageTitleByIdentifier($this->getData('href'));
        } else if ($this->getData('page_id')) {
            $this->_anchorText = $this->_resourcePage->getCmsPageTitleById($this->getData('page_id'));
        } else {
            $this->_anchorText = $this->getData('href');
        }

        return $this->_anchorText;
    }
}
