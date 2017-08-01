<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Backend Catalog Price Rules controller
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\CatalogRule\Controller\Adminhtml\Promo;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\Filter\Date;

/**
 * Class \Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog
 *
 * @since 2.0.0
 */
abstract class Catalog extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_CatalogRule::promo_catalog';

    /**
     * Dirty rules notice message
     *
     *
     * @var string
     * @since 2.0.0
     */
    protected $_dirtyRulesNoticeMessage;

    /**
     * Core registry
     *
     * @var Registry
     * @since 2.0.0
     */
    protected $_coreRegistry = null;

    /**
     * Date filter instance
     *
     * @var \Magento\Framework\Stdlib\DateTime\Filter\Date
     * @since 2.0.0
     */
    protected $_dateFilter;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Registry $coreRegistry
     * @param Date $dateFilter
     * @since 2.0.0
     */
    public function __construct(Context $context, Registry $coreRegistry, Date $dateFilter)
    {
        parent::__construct($context);
        $this->_coreRegistry = $coreRegistry;
        $this->_dateFilter = $dateFilter;
    }

    /**
     * Init action
     *
     * @return $this
     * @since 2.0.0
     */
    protected function _initAction()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu(
            'Magento_CatalogRule::promo_catalog'
        )->_addBreadcrumb(
            __('Promotions'),
            __('Promotions')
        );
        return $this;
    }

    /**
     * Set dirty rules notice message
     *
     * @param string $dirtyRulesNoticeMessage
     * @return void
     * @codeCoverageIgnore
     * @since 2.0.0
     */
    public function setDirtyRulesNoticeMessage($dirtyRulesNoticeMessage)
    {
        $this->_dirtyRulesNoticeMessage = $dirtyRulesNoticeMessage;
    }

    /**
     * Get dirty rules notice message
     *
     * @return string
     * @since 2.0.0
     */
    public function getDirtyRulesNoticeMessage()
    {
        $defaultMessage = __(
            'We found updated rules that are not applied. Please click "Apply Rules" to update your catalog.'
        );
        return $this->_dirtyRulesNoticeMessage ? $this->_dirtyRulesNoticeMessage : $defaultMessage;
    }
}
