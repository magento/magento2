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
namespace Magento\Catalog\Test\Page\Product;

use Mtf\Page\Page;
use Mtf\Factory\Factory;

/**
 * Backend product review page
 *
 */
class CatalogProductReview extends Page
{
    /**
     * URL for catalog product review
     */
    const MCA = 'review/product';

    /**
     * Review grid selector
     *
     * @var string
     */
    protected $gridSelector = '#reviwGrid';

    /**
     * Edit form review selector
     *
     * @var string
     */
    protected $editFormSelector = '#anchor-content';

    /**
     * Messages selector
     *
     * @var string
     */
    protected $messageWrapperSelector = '#messages';

    /**
     * {@inheritdoc}
     */
    protected function _init()
    {
        $this->_url = $_ENV['app_backend_url'] . self::MCA;
    }

    /**
     * Get product reviews grid
     *
     * @return \Magento\Review\Test\Block\Adminhtml\Grid
     */
    public function getGridBlock()
    {
        return Factory::getBlockFactory()->getMagentoReviewAdminhtmlGrid($this->_browser->find($this->gridSelector));
    }

    /**
     * Get review edit form
     *
     * @return \Magento\Review\Test\Block\Adminhtml\Edit
     */
    public function getEditForm()
    {
        return Factory::getBlockFactory()->getMagentoReviewAdminhtmlEdit(
            $this->_browser->find($this->editFormSelector)
        );
    }

    /**
     * Get messages block
     *
     * @return \Magento\Core\Test\Block\Messages
     */
    public function getMessagesBlock()
    {
        return Factory::getBlockFactory()->getMagentoCoreMessages($this->_browser->find($this->messageWrapperSelector));
    }
}
