<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Account;

use Magento\Customer\Block\Account\SortLinkInterface;

/**
 * Class Link
 *
 * @api
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @since 2.0.0
 */
class Link extends \Magento\Framework\View\Element\Html\Link implements SortLinkInterface
{
    /**
     * @var \Magento\Customer\Model\Url
     * @since 2.0.0
     */
    protected $_customerUrl;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Url $customerUrl,
        array $data = []
    ) {
        $this->_customerUrl = $customerUrl;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getHref()
    {
        return $this->_customerUrl->getAccountUrl();
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }
}
