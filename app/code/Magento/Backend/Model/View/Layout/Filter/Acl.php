<?php
/**
 * ACL block filter
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Backend\Model\View\Layout\Filter;

use Magento\Framework\View\Layout\ScheduledStructure;
use Magento\Framework\View\Layout\Data\Structure;
use Magento\Backend\Model\View\Layout\FilterInterface;
use Magento\Backend\Model\View\Layout\StructureManager;
use Magento\Framework\App\ObjectManager;

/**
 * Class Acl
 *
 * Manage visibility of ui components and blocks at the backend according to ACL resources.
 * If user role has corresponding resource block will be displayed.
 *
 * @see usage details in \Magento\Backend\Model\View\Layout\FilterInterface description
 *
 * Example of declaration in layout
 *
 * <uiComponent name="form" acl="Resource::name" />
 */
class Acl implements FilterInterface
{
    /**
     * Authorization
     *
     * @var \Magento\Framework\AuthorizationInterface
     */
    protected $authorization;

    /**
     * @var StructureManager
     */
    private $structureManager;

    /**
     * @param \Magento\Framework\AuthorizationInterface $authorization
     */
    public function __construct(\Magento\Framework\AuthorizationInterface $authorization)
    {
        $this->authorization = $authorization;
    }

    /**
     * @return StructureManager
     */
    private function getStructureManager()
    {
        if (!$this->structureManager) {
            $this->structureManager = ObjectManager::getInstance()->get(StructureManager::class);
        }
        return $this->structureManager;
    }

    /**
     * Delete elements that have "acl" attribute but value is "not allowed"
     * In any case, the "acl" attribute will be unset
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     *
     * @return void
     */
    public function filterAclElements(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        foreach ($scheduledStructure->getElements() as $name => $data) {
            list(, $data) = $data;
            if (isset($data['attributes']['acl']) && $data['attributes']['acl']) {
                if (!$this->authorization->isAllowed($data['attributes']['acl'])) {
                    $this->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
    }

    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function removeElement(
        ScheduledStructure $scheduledStructure,
        Structure $structure,
        $elementName,
        $isChild = false
    ) {
        $this->getStructureManager()->removeElement($scheduledStructure, $structure, $elementName, $isChild);
        return $this;
    }

    /**
     * @param ScheduledStructure $scheduledStructure
     * @param Structure $structure
     * @return bool
     */
    public function filterElement(ScheduledStructure $scheduledStructure, Structure $structure)
    {
        $this->filterAclElements($scheduledStructure, $structure);
        return true;
    }
}
