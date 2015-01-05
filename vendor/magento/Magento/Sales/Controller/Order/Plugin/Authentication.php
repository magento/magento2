<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Sales\Controller\Order\Plugin;

use Magento\Framework\App\RequestInterface;

class Authentication
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerUrl;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @param \Magento\Customer\Model\Url $customerUrl
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Customer\Model\Url $customerUrl,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->customerUrl = $customerUrl;
        $this->customerSession = $customerSession;
    }

    /**
     * Authenticate user
     *
     * @param \Magento\Framework\App\ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     */
    public function beforeDispatch(\Magento\Framework\App\ActionInterface $subject, RequestInterface $request)
    {
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($subject, $loginUrl)) {
            $subject->getActionFlag()->set('', $subject::FLAG_NO_DISPATCH, true);
        }
    }
}
