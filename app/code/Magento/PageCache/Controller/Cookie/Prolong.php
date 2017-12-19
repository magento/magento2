<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Controller\Cookie;

/**
 * Prolong cookie action.
 */
class Prolong extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\PageCache\Model\Cookie\Prolongation\Frontend
     */
    protected $_frontendCookieProlongation;

    /**
     * Constructor.
     *
     * @param \Magento\PageCache\Model\Cookie\Prolongation\Frontend $frontendCookieProlongation
     * @param \Magento\Framework\App\Action\Context                 $context
     */
    public function __construct(
        \Magento\PageCache\Model\Cookie\Prolongation\Frontend $frontendCookieProlongation,
        \Magento\Framework\App\Action\Context $context
    ) {
        $this->_frontendCookieProlongation = $frontendCookieProlongation;
        parent::__construct($context);
    }

    /**
     * Prolong cookie action.
     *
     * @return void
     */
    public function execute()
    {
        $this->_frontendCookieProlongation->execute();
    }
}
