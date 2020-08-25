<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\LoginAsCustomerAdminUi\Ui\Customer\Component\ConfirmationPopup;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Escaper;
use Magento\Store\Model\Group;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Store\Model\Website;

/**
 * Store group options for Login As Customer confirmation pop-up.
 */
class Options implements OptionSourceInterface
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var Share
     */
    private $share;

    /**
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var array
     */
    private $options;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Escaper $escaper
     * @param RequestInterface $request
     * @param Share $share
     * @param SystemStore $systemStore
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Escaper $escaper,
        RequestInterface $request,
        Share $share,
        SystemStore $systemStore
    ) {
        $this->customerRepository = $customerRepository;
        $this->escaper = $escaper;
        $this->request = $request;
        $this->share = $share;
        $this->systemStore = $systemStore;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        if ($this->options !== null) {
            return $this->options;
        }

        $customerId = (int)$this->request->getParam('id');
        $this->options = $this->generateCurrentOptions($customerId);

        return $this->options;
    }

    /**
     * Sanitize website/store option name.
     *
     * @param string $name
     *
     * @return string
     */
    private function sanitizeName(string $name): string
    {
        $matches = [];
        preg_match('/\$[:]*{(.)*}/', $name, $matches);
        if (count($matches) > 0) {
            $name = $this->escaper->escapeHtml($this->escaper->escapeJs($name));
        } else {
            $name = $this->escaper->escapeHtml($name);
        }

        return $name;
    }

    /**
     * Generate current options.
     *
     * @param int $customerId
     * @return array
     */
    private function generateCurrentOptions(int $customerId): array
    {
        $options = [];
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $websiteCollection = $this->systemStore->getWebsiteCollection();
            /** @var Website $website */
            foreach ($websiteCollection as $website) {
                $groups = $this->fillStoreGroupOptions($website, $customer);
                if (!empty($groups)) {
                    $code = $website->getCode();
                    $name = $this->sanitizeName($website->getName());
                    $options[$code]['label'] = $name;
                    $options[$code]['value'] = $groups;
                }
            }
        }

        return $options;
    }

    /**
     * Fill Store Group options array.
     *
     * @param Website $website
     * @param CustomerInterface $customer
     * @return array
     */
    private function fillStoreGroupOptions(Website $website, CustomerInterface $customer): array
    {
        $groups = [];
        $groupCollection = $this->systemStore->getGroupCollection();
        $isGlobalScope = $this->share->isGlobalScope();
        $customerWebsiteId = $customer->getWebsiteId();
        $customerStoreId = $customer->getStoreId();
        $websiteId = $website->getId();
        /** @var Group $group */
        foreach ($groupCollection as $group) {
            if ($group->getWebsiteId() == $websiteId) {
                $storeViewIds = $group->getStoreIds();
                if (!empty($storeViewIds)) {
                    $code = $group->getCode();
                    $name = $this->sanitizeName($group->getName());
                    $groups[$code]['label'] = str_repeat(' ', 4) . $name;
                    $groups[$code]['value'] = array_values($storeViewIds)[0];
                    $groups[$code]['disabled'] = !$isGlobalScope && $customerWebsiteId !== $websiteId;
                    $groups[$code]['selected'] = in_array($customerStoreId, $storeViewIds) ? true : false;
                }
            }
        }

        return $groups;
    }
}
