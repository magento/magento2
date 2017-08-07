<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogRule\Model;

use Magento\CatalogRule\Api\Data;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\ValidatorException;

/**
 * Class \Magento\CatalogRule\Model\CatalogRuleRepository
 *
 * @since 2.1.0
 */
class CatalogRuleRepository implements \Magento\CatalogRule\Api\CatalogRuleRepositoryInterface
{
    /**
     * @var ResourceModel\Rule
     * @since 2.1.0
     */
    protected $ruleResource;

    /**
     * @var RuleFactory
     * @since 2.1.0
     */
    protected $ruleFactory;

    /**
     * @var array
     * @since 2.1.0
     */
    private $rules = [];

    /**
     * @param ResourceModel\Rule $ruleResource
     * @param RuleFactory $ruleFactory
     * @since 2.1.0
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\Rule $ruleResource,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory
    ) {
        $this->ruleResource = $ruleResource;
        $this->ruleFactory = $ruleFactory;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function save(Data\RuleInterface $rule)
    {
        if ($rule->getRuleId()) {
            $rule = $this->get($rule->getRuleId())->addData($rule->getData());
        }

        try {
            $this->ruleResource->save($rule);
            unset($this->rules[$rule->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save rule %1', $rule->getRuleId()));
        }
        return $rule;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function get($ruleId)
    {
        if (!isset($this->rules[$ruleId])) {
            /** @var \Magento\CatalogRule\Model\Rule $rule */
            $rule = $this->ruleFactory->create();

            /* TODO: change to resource model after entity manager will be fixed */
            $rule->load($ruleId);
            if (!$rule->getRuleId()) {
                throw new NoSuchEntityException(__('Rule with specified ID "%1" not found.', $ruleId));
            }
            $this->rules[$ruleId] = $rule;
        }
        return $this->rules[$ruleId];
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function delete(Data\RuleInterface $rule)
    {
        try {
            $this->ruleResource->delete($rule);
            unset($this->rules[$rule->getId()]);
        } catch (ValidatorException $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove rule %1', $rule->getRuleId()));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     * @since 2.1.0
     */
    public function deleteById($ruleId)
    {
        $model = $this->get($ruleId);
        $this->delete($model);
        return true;
    }
}
