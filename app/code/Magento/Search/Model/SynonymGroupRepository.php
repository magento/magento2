<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Search\Api\SynonymGroupRepositoryInterface;
use Magento\Search\Model\ResourceModel\SynonymGroup as SynonymGroupResourceModel;

/**
 * Synonym Group repository, provides implementation of saving and deleting synonym groups
 */
class SynonymGroupRepository implements SynonymGroupRepositoryInterface
{
    /**
     * SynonymGroup Factory
     *
     * @var SynonymGroupFactory
     */
    protected $synonymGroupFactory;

    /**
     * SynonymGroup resource model
     *
     * @var SynonymGroupResourceModel
     */
    protected $resourceModel;

    /**
     * Constructor
     *
     * @param SynonymGroupFactory $synonymGroupFactory
     * @param SynonymGroupResourceModel $resourceModel
     */
    public function __construct(
        \Magento\Search\Model\SynonymGroupFactory $synonymGroupFactory,
        SynonymGroupResourceModel $resourceModel
    ) {
        $this->synonymGroupFactory = $synonymGroupFactory;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Saves a synonym group
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return void
     * @throws \Exception
     */
    public function save(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup)
    {
        /** @var SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->synonymGroupFactory->create();
        $synonymGroupModel->load($synonymGroup->getGroupId());
        $isCreate = $synonymGroupModel->getSynonymGroup() === null;
        if ($isCreate) {
            $this->create($synonymGroup);
        } else {
            $this->update($synonymGroupModel, $synonymGroup);
        }
    }

    /**
     * Deletes a synonym group
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return void
     */
    public function delete(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup)
    {
        try {
            $this->resource->delete($synonymGroup);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * Private helper to create a synonym group, throw exception on merge conflict
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return void
     * @throws \Exception
     */
    private function create(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup)
    {
        $matchingSynonymGroups = $this->getMatchingSynonymGroups($synonymGroup);
        if ($matchingSynonymGroups) {
            throw new \Exception($this->getExceptionMessage($matchingSynonymGroups));
        }
        /** @var SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->synonymGroupFactory->create();
        $synonymGroupModel->setWebsiteId($synonymGroup->getWebsiteId());
        $synonymGroupModel->setStoreId($synonymGroup->getStoreId());
        $synonymGroupModel->setSynonymGroup($synonymGroup->getSynonymGroup());
        $this->resourceModel->save($synonymGroupModel);
    }

    /**
     * Private helper to update a synonym group, throw exception on merge conflict
     *
     * @param SynonymGroup $oldSynonymGroup
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $newSynonymGroup
     * @return void
     * @throws \Exception
     */
    private function update(
        SynonymGroup $oldSynonymGroup,
        \Magento\Search\Api\Data\SynonymGroupInterface $newSynonymGroup
    ) {
        $matchingSynonymGroups = $this->getMatchingSynonymGroups($newSynonymGroup);
        $matchingSynonymGroups = array_diff($matchingSynonymGroups, [$oldSynonymGroup->getSynonymGroup()]);
        if ($matchingSynonymGroups) {
            throw new \Exception($this->getExceptionMessage($matchingSynonymGroups));
        }
        $oldSynonymGroup->setWebsiteId($newSynonymGroup->getWebsiteId());
        $oldSynonymGroup->setStoreId($newSynonymGroup->getStoreId());
        $oldSynonymGroup->setSynonymGroup($newSynonymGroup->getSynonymGroup());
        $this->resourceModel->save($oldSynonymGroup);
    }

    /**
     * Gets merge conflict exception message
     *
     * @param string[] $matchingSynonymGroups
     * @return string
     */
    private function getExceptionMessage($matchingSynonymGroups)
    {
        $displayString = 'Merge conflict with current synonym groups: ';
        $displayString .= '(';
        $displayString .= implode('), (', $matchingSynonymGroups);
        $displayString .= ')';
        return $displayString;
    }

    /**
     * Gets all other matching synonym groups in the same scope
     *
     * @param \Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup
     * @return string[]
     */
    private function getMatchingSynonymGroups(\Magento\Search\Api\Data\SynonymGroupInterface $synonymGroup)
    {
        $synonymGroupsInScope = $this->resourceModel->getByScope(
            $synonymGroup->getWebsiteId(),
            $synonymGroup->getStoreId()
        );
        $matchingSynonymGroups = [];
        foreach ($synonymGroupsInScope as $synonymGroupInScope) {
            if (array_intersect(
                explode(',', $synonymGroup->getSynonymGroup()),
                explode(',', $synonymGroupInScope['synonyms']))
            ) {
                $matchingSynonymGroups[] = $synonymGroupInScope['synonyms'];
            }
        }
        return $matchingSynonymGroups;
    }
}
