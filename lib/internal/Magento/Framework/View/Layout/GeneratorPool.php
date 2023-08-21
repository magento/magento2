<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\View\Layout\Condition\ConditionFactory;
use Psr\Log\LoggerInterface;

/**
 * Pool of generators for structural elements
 * @api
 * @since 100.0.2
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorPool
{
    /**
     * @var ScheduledStructure\Helper
     */
    protected $helper;

    /**
     * @var GeneratorInterface[]
     */
    protected $generators = [];

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var ConditionFactory
     */
    private $conditionFactory;

    /**
     * @var State
     */
    private $state;

    /**
     * @param ScheduledStructure\Helper $helper
     * @param ConditionFactory $conditionFactory
     * @param LoggerInterface $logger
     * @param array|null $generators
     * @param State|null $state
     */
    public function __construct(
        ScheduledStructure\Helper $helper,
        ConditionFactory $conditionFactory,
        LoggerInterface $logger,
        array $generators = null,
        ?State $state = null
    ) {
        $this->helper = $helper;
        $this->conditionFactory = $conditionFactory;
        $this->logger = $logger;
        $this->addGenerators($generators);
        $this->state = $state ?? ObjectManager::getInstance()->get(State::class);
    }

    /**
     * Get generator
     *
     * @param string $type
     * @return GeneratorInterface
     * @throws \InvalidArgumentException
     */
    public function getGenerator($type)
    {
        if (!isset($this->generators[$type])) {
            throw new \InvalidArgumentException("Invalid generator type '{$type}'");
        }
        return $this->generators[$type];
    }

    /**
     * Traverse through all generators and generate all scheduled elements.
     *
     * @param Reader\Context $readerContext
     * @param Generator\Context $generatorContext
     * @return $this
     */
    public function process(Reader\Context $readerContext, Generator\Context $generatorContext)
    {
        $this->buildStructure($readerContext->getScheduledStructure(), $generatorContext->getStructure());
        foreach ($this->generators as $generator) {
            $generator->process($readerContext, $generatorContext);
        }
        return $this;
    }

    /**
     * Add generators to pool
     *
     * @param GeneratorInterface[] $generators
     *
     * @return void
     */
    protected function addGenerators(array $generators)
    {
        foreach ($generators as $generator) {
            if (!$generator instanceof GeneratorInterface) {
                throw new \InvalidArgumentException(
                    sprintf('Generator class must be an instance of %s', GeneratorInterface::class)
                );
            }
            $this->generators[$generator->getType()] = $generator;
        }
    }

    /**
     * Build structure that is based on scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @return $this
     */
    protected function buildStructure(ScheduledStructure $scheduledStructure, Data\Structure $structure)
    {
        //Schedule all element into nested structure
        while (false === $scheduledStructure->isStructureEmpty()) {
            $this->helper->scheduleElement($scheduledStructure, $structure, key($scheduledStructure->getStructure()));
        }
        $scheduledStructure->flushPaths();
        while (false === $scheduledStructure->isListToSortEmpty()) {
            $this->reorderElements($scheduledStructure, $structure, key($scheduledStructure->getListToSort()));
        }
        foreach ($scheduledStructure->getListToMove() as $elementToMove) {
            $this->moveElementInStructure($scheduledStructure, $structure, $elementToMove);
        }
        foreach ($scheduledStructure->getListToRemove() as $elementToRemove) {
            $this->removeElement($scheduledStructure, $structure, $elementToRemove);
        }
        foreach ($scheduledStructure->getElements() as $name => $data) {
            list(, $data) = $data;
            if ($this->visibilityConditionsExistsIn($data)) {
                $condition = $this->conditionFactory->create($data['attributes']['visibilityConditions']);
                if (!$condition->isVisible($data['attributes']['visibilityConditions'])) {
                    $this->removeElement($scheduledStructure, $structure, $name);
                }
            }
        }
        return $this;
    }

    /**
     * Reorder a child of a specified element.
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @param string $elementName
     * @return void
     */
    protected function reorderElements(
        ScheduledStructure $scheduledStructure,
        Data\Structure $structure,
        $elementName
    ) {
        $element = $scheduledStructure->getElementToSort($elementName);
        $scheduledStructure->unsetElementToSort($element[ScheduledStructure::ELEMENT_NAME]);

        if (isset($element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING])) {
            $siblingElement = $scheduledStructure->getElementToSort(
                $element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING]
            );

            if (isset($siblingElement[ScheduledStructure::ELEMENT_NAME])
                    && $structure->hasElement($siblingElement[ScheduledStructure::ELEMENT_NAME])
            ) {
                $this->reorderElements(
                    $scheduledStructure,
                    $structure,
                    $siblingElement[ScheduledStructure::ELEMENT_NAME]
                );
            }
        }

        $structure->reorderChildElement(
            $element[ScheduledStructure::ELEMENT_PARENT_NAME],
            $element[ScheduledStructure::ELEMENT_NAME],
            $element[ScheduledStructure::ELEMENT_OFFSET_OR_SIBLING],
            $element[ScheduledStructure::ELEMENT_IS_AFTER]
        );
    }

    /**
     * Remove scheduled element
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @param string $elementName
     * @param bool $isChild
     * @return $this
     */
    protected function removeElement(
        ScheduledStructure $scheduledStructure,
        Data\Structure $structure,
        $elementName,
        $isChild = false
    ) {
        $elementsToRemove = array_keys($structure->getChildren($elementName));
        $scheduledStructure->unsetElement($elementName);
        foreach ($elementsToRemove as $element) {
            $this->removeElement($scheduledStructure, $structure, $element, true);
        }
        if (!$isChild) {
            $structure->unsetElement($elementName);
            $scheduledStructure->unsetElementFromListToRemove($elementName);
        }
        return $this;
    }

    /**
     * Move element in scheduled structure
     *
     * @param ScheduledStructure $scheduledStructure
     * @param Data\Structure $structure
     * @param string $element
     * @return $this
     */
    protected function moveElementInStructure(
        ScheduledStructure $scheduledStructure,
        Data\Structure $structure,
        $element
    ) {
        list($destination, $siblingName, $isAfter, $alias) = $scheduledStructure->getElementToMove($element);
        $childAlias = $structure->getChildAlias($structure->getParentId($element), $element);
        if (!$alias && false === $structure->getChildId($destination, $childAlias)) {
            $alias = $childAlias;
        }
        $structure->unsetChild($element, $alias);
        try {
            $structure->setAsChild($element, $destination, $alias);
            $structure->reorderChildElement($destination, $element, $siblingName, $isAfter);
        } catch (\OutOfBoundsException $e) {
            if ($this->state->getMode() === State::MODE_DEVELOPER) {
                $this->logger->warning('Broken reference: ' . $e->getMessage());
            }
        }
        $scheduledStructure->unsetElementFromBrokenParentList($element);
        return $this;
    }

    /**
     * Check visibility conditions exists in data.
     *
     * @param array $data
     *
     * @return bool
     * @since 101.0.0
     */
    protected function visibilityConditionsExistsIn(array $data)
    {
        return isset($data['attributes']) &&
            array_key_exists('visibilityConditions', $data['attributes']) &&
            !empty($data['attributes']['visibilityConditions']);
    }
}
