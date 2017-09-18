<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Model;

use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Search\Api\Data\SynonymGroupInterface;
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
     * {@inheritdoc}
     */
    public function save(SynonymGroupInterface $synonymGroup, $errorOnMergeConflict = false)
    {
        /** @var SynonymGroup $synonymGroupModel */
        $synonymGroupModel = $this->synonymGroupFactory->create();
        $synonymGroupModel->load($synonymGroup->getGroupId());
        $isCreate = $synonymGroupModel->getSynonymGroup() === null;
        if ($isCreate) {
            return $this->create($synonymGroup, $errorOnMergeConflict);
        } else {
            return $this->update($synonymGroupModel, $synonymGroup, $errorOnMergeConflict);
        }
    }

    /**
     * Deletes a synonym group
     *
     * @param SynonymGroupInterface $synonymGroup
     * @throws CouldNotDeleteException
     * @return bool
     */
    public function delete(SynonymGroupInterface $synonymGroup)
    {
        try {
            $this->resourceModel->delete($synonymGroup);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __(
                    'Synonym group with id %1 cannot be deleted. %2',
                    $synonymGroup->getGroupId(),
                    $exception->getMessage()
                )
            );
        }
        return true;
    }

    /**
     * Return a particular synonym group interface instance based on passed in synonym group id
     *
     * @param int $synonymGroupId
     * @return \Magento\Search\Api\Data\SynonymGroupInterface
     */
    public function get($synonymGroupId)
    {
        /** @var SynonymGroup $synonymGroup */
        $synonymGroup = $this->synonymGroupFactory->create();
        if ($synonymGroupId !== null) {
            $synonymGroup->load($synonymGroupId);
        }
        return $synonymGroup;
    }

    /**
     * Private helper to create a synonym group, throw exception on merge conflict
     *
     * @param SynonymGroupInterface $synonymGroup
     * @param bool $errorOnMergeConflict
     * @return SynonymGroupInterface
     * @throws Synonym\MergeConflictException
     */
    private function create(SynonymGroupInterface $synonymGroup, $errorOnMergeConflict)
    {
        $matchingSynonymGroups = $this->getMatchingSynonymGroups($synonymGroup);
        if ($matchingSynonymGroups) {
            if ($errorOnMergeConflict) {
                throw new Synonym\MergeConflictException(
                    $this->parseToArray($matchingSynonymGroups),
                    $this->getExceptionMessage($matchingSynonymGroups)
                );
            }
            // merge matching synonyms before creating a new row
            $mergedSynonyms = $this->merge($synonymGroup, array_keys($matchingSynonymGroups));
            /** @var SynonymGroup $newSynonymGroupModel */
            $newSynonymGroupModel = $this->synonymGroupFactory->create();
            $newSynonymGroupModel->setSynonymGroup(implode(',', $mergedSynonyms));
            $newSynonymGroupModel->setWebsiteId($synonymGroup->getWebsiteId());
            $newSynonymGroupModel->setStoreId($synonymGroup->getStoreId());
            $this->resourceModel->save($newSynonymGroupModel);
            $synonymGroup->setSynonymGroup($newSynonymGroupModel->getSynonymGroup());
            $synonymGroup->setGroupId($newSynonymGroupModel->getGroupId());
        } else {
            // no merge conflict, perform simple insert
            /** @var SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->synonymGroupFactory->create();
            $this->populateSynonymGroupModel($synonymGroupModel, $synonymGroup);
            $this->resourceModel->save($synonymGroupModel);
            $synonymGroup->setGroupId($synonymGroupModel->getGroupId());
        }
        return $synonymGroup;
    }

    /**
     * Perform synonyms merge
     *
     * @param SynonymGroupInterface $synonymGroupToMerge
     * @param array $matchingGroupIds
     * @return array
     */
    private function merge(SynonymGroupInterface $synonymGroupToMerge, array $matchingGroupIds)
    {
        $mergedSynonyms = [];
        foreach ($matchingGroupIds as $groupId) {
            /** @var SynonymGroup $synonymGroupModel */
            $synonymGroupModel = $this->synonymGroupFactory->create();
            $synonymGroupModel->load($groupId);
            $mergedSynonyms = array_merge($mergedSynonyms, explode(',', $synonymGroupModel->getSynonymGroup()));
            $synonymGroupModel->delete();
        }
        $mergedSynonyms = array_merge($mergedSynonyms, explode(',', $synonymGroupToMerge->getSynonymGroup()));
        $mergedSynonyms = array_unique($mergedSynonyms);
        return $mergedSynonyms;
    }

    /**
     * Private helper to populate SynonymGroup model with data
     *
     * @param SynonymGroup $modelToPopulate
     * @param SynonymGroupInterface $synonymGroupData
     * @return void
     */
    private function populateSynonymGroupModel(SynonymGroup $modelToPopulate, SynonymGroupInterface $synonymGroupData)
    {
        $modelToPopulate->setWebsiteId($synonymGroupData->getWebsiteId());
        $modelToPopulate->setStoreId($synonymGroupData->getStoreId());
        $modelToPopulate->setSynonymGroup($synonymGroupData->getSynonymGroup());
    }

    /**
     * Private helper to update a synonym group, throw exception on merge conflict
     *
     * @param SynonymGroup $oldSynonymGroup
     * @param SynonymGroupInterface $newSynonymGroup
     * @param bool $errorOnMergeConflict
     * @return SynonymGroupInterface
     * @throws Synonym\MergeConflictException
     */
    private function update(
        SynonymGroup $oldSynonymGroup,
        SynonymGroupInterface $newSynonymGroup,
        $errorOnMergeConflict
    ) {
        $matchingSynonymGroups = $this->getMatchingSynonymGroups($newSynonymGroup);
        // ignore existing synonym group as it's value will be discarded
        $matchingSynonymGroups = array_diff_key(
            $matchingSynonymGroups,
            [$oldSynonymGroup->getGroupId() => $oldSynonymGroup->getSynonymGroup()]
        );
        if ($matchingSynonymGroups) {
            if ($errorOnMergeConflict) {
                throw new Synonym\MergeConflictException(
                    $this->parseToArray($matchingSynonymGroups),
                    $this->getExceptionMessage($matchingSynonymGroups)
                );
            }
            // merge matching synonyms before updating a row
            $mergedSynonyms = $this->merge($newSynonymGroup, array_keys($matchingSynonymGroups));
            $oldSynonymGroup->setSynonymGroup(implode(',', $mergedSynonyms));
            $oldSynonymGroup->setWebsiteId($newSynonymGroup->getWebsiteId());
            $oldSynonymGroup->setStoreId($newSynonymGroup->getStoreId());
            $this->resourceModel->save($oldSynonymGroup);
        } else {
            // no merge conflict, perform simple update
            $this->populateSynonymGroupModel($oldSynonymGroup, $newSynonymGroup);
            $this->resourceModel->save($oldSynonymGroup);
        }
        return $oldSynonymGroup;
    }

    /**
     * Gets merge conflict exception message
     *
     * @param string[] $matchingSynonymGroups
     * @return \Magento\Framework\Phrase
     */
    private function getExceptionMessage($matchingSynonymGroups)
    {
        $displayString = '(';
        $displayString .= implode('), (', $matchingSynonymGroups);
        $displayString .= ')';
        return __('Merge conflict with existing synonym group(s): %1', $displayString);
    }

    /**
     * Parse the matching synonym groups into array
     *
     * @param string[] $matchingSynonymGroups
     * @return array
     */
    private function parseToArray($matchingSynonymGroups)
    {
        $parsedArray = [];
        foreach ($matchingSynonymGroups as $matchingSynonymGroup) {
            $parsedArray[] = explode(',', $matchingSynonymGroup);
        }
        return $parsedArray;
    }

    /**
     * Gets all other matching synonym groups in the same scope
     *
     * @param SynonymGroupInterface $synonymGroup
     * @return string[]
     */
    private function getMatchingSynonymGroups(SynonymGroupInterface $synonymGroup)
    {
        $synonymGroupsInScope = $this->resourceModel->getByScope(
            $synonymGroup->getWebsiteId(),
            $synonymGroup->getStoreId()
        );
        $matchingSynonymGroups = [];
        foreach ($synonymGroupsInScope as $synonymGroupInScope) {
            if (array_intersect(
                explode(',', $synonymGroup->getSynonymGroup()),
                explode(',', $synonymGroupInScope['synonyms'])
            )) {
                $matchingSynonymGroups[$synonymGroupInScope['group_id']] = $synonymGroupInScope['synonyms'];
            }
        }
        return $matchingSynonymGroups;
    }
}
