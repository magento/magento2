<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model;

use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Escaper;
use Magento\Variable\Model\ResourceModel\Variable as VariableResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Custom variable model
 *
 * @method \Magento\Variable\Model\ResourceModel\Variable _getResource()
 * @method \Magento\Variable\Model\ResourceModel\Variable getResource()
 * @method string getCode()
 * @method \Magento\Variable\Model\Variable setCode(string $value)
 * @method string getName()
 * @method \Magento\Variable\Model\Variable setName(string $value)
 *
 * @api
 */
class Variable extends AbstractModel
{
    const TYPE_TEXT = 'text';

    const TYPE_HTML = 'html';

    /**
     * @var int
     */
    protected $storeId = 0;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper = null;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Variable constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Variable\Model\ResourceModel\Variable $resource
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Escaper $escaper,
        VariableResource $resource,
        StoreManagerInterface $storeManager,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->escaper = $escaper;
        $this->storeManager = $storeManager;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Internal Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(VariableResource::class);
    }

    /**
     * Setter
     *
     * @param integer $storeId
     * @return $this
     * @codeCoverageIgnore
     */
    public function setStoreId($storeId)
    {
        $this->storeId = $storeId;
        return $this;
    }

    /**
     * Getter
     *
     * @return integer
     * @codeCoverageIgnore
     */
    public function getStoreId()
    {
        return $this->storeId;
    }

    /**
     * Load variable by code
     *
     * @param string $code
     * @return $this
     * @codeCoverageIgnore
     */
    public function loadByCode($code)
    {
        $this->getResource()->loadByCode($this, $code);
        return $this;
    }

    /**
     * Return variable value depend on given type
     *
     * @param string $type
     * @return string
     */
    public function getValue($type = null)
    {
        if ($type === null) {
            $type = self::TYPE_HTML;
        }
        if ($type == self::TYPE_TEXT || !strlen((string)$this->getData('html_value'))) {
            $value = $this->getData('plain_value');
            //escape html if type is html, but html value is not defined
            if ($type == self::TYPE_HTML) {
                $value = nl2br($this->escaper->escapeHtml($value));
            }
            return $value;
        }
        return $this->getData('html_value');
    }

    /**
     * Validation of object data. Checking for unique variable code
     *
     * @return \Magento\Framework\Phrase|bool
     */
    public function validate()
    {
        if ($this->getCode() && $this->getName()) {
            $variable = $this->getResource()->getVariableByCode($this->getCode());
            if (!empty($variable) && $variable['variable_id'] != $this->getId()) {
                return __('Variable Code must be unique.');
            }
            return true;
        }
        return __('Validation has failed.');
    }

    /**
     * Retrieve variables option array
     * @todo: extract method as separate class
     * @param bool $withGroup
     * @return array
     */
    public function getVariablesOptionArray($withGroup = false)
    {
        /* @var $collection \Magento\Variable\Model\ResourceModel\Variable\Collection */
        $collection = $this->getCollection();
        $variables = [];
        foreach ($collection->toOptionArray() as $variable) {
            $variables[] = [
                'value' => '{{customVar code=' . $variable['value'] . '}}',
                'label' => __('%1', $variable['label']),
            ];
        }
        if ($withGroup && $variables) {
            $variables = ['label' => __('Custom Variables'), 'value' => $variables];
        }
        return $variables;
    }

    /**
     * Check if the given store (or the current one) has the given
     * variable set with the given text value
     *
     * @param string $variableCode
     * @param string $expectedValue
     * @param string $type
     * @param int $storeId
     *
     * @return boolean
     */
    public function customVariableHasValue(
        $variableCode,
        $expectedValue,
        $type = self::TYPE_TEXT,
        $storeId = null
    ) {
        $status = false;

        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }

        $this->setStoreId($storeId)
            ->loadByCode($variableCode);

        $value = $this->getValue($type);

        if ($value && $value === (string) $expectedValue) {
            $status = true;
        }

        return $status;
    }
}
