<?php

namespace Liip\CustomerHierarchy\Block\Customer\Accounts;

class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @param \Magento\Customer\Model\Customer $customer
     * @return $this
     */
    public function setCustomer(\Magento\Customer\Model\Customer $customer)
    {
        $this->customer = $customer;
        return $this;
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    private function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param string $alias
     * @param bool $useCache
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getChildHtml($alias = '', $useCache = false)
    {
        $layout = $this->getLayout();
        if ($layout) {
            $name = $this->getNameInLayout();
            foreach ($layout->getChildBlocks($name) as $child) {
                $child->setCustomer($this->getCustomer());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
