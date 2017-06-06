<?php

namespace Liip\CustomerHierarchy\Block\Customer\Roles;

class Container extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Liip\CustomerHierarchy\Model\Role
     */
    private $role;

    /**
     * @param \Liip\CustomerHierarchy\Model\Role $role
     * @return $this
     */
    public function setRole(\Liip\CustomerHierarchy\Model\Role $role)
    {
        $this->role = $role;
        return $this;
    }

    /**
     * @return \Liip\CustomerHierarchy\Model\Role
     */
    private function getRole()
    {
        return $this->role;
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
                $child->setRole($this->getRole());
            }
        }
        return parent::getChildHtml($alias, $useCache);
    }
}
