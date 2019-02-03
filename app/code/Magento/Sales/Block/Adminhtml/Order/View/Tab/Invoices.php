<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order Invoices grid
 *
 * @api
 * @since 100.0.2
 */
class Invoices extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * @var Magento\Framework\AuthorizationInterface
     */
    private $authorization;
    
    /**
	 *
	 * @param \Magento\Framework\View\Element\Context $context
	 * @param array $data
	 * @param \Magento\Framework\AuthorizationInterface|null $authorization
	 */
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
		array $data = [],
		\Magento\Framework\AuthorizationInterface $authorization = null
	) {
		$this->authorization = $authorization?: \Magento\Framework\App\ObjectManager::getInstance()->get(Magento\Framework\AuthorizationInterface::class);
		parent::__construct($context, $data);
	}
    
    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Invoices');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return $this->authorization->isAllowed('Magento_Sales::invoice');
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
