<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Escaper;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\Validator\HTML\WYSIWYGValidatorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Variable\Model\ResourceModel\Variable\Collection;

/**
 * Custom variable model
 *
 * @method string getCode()
 * @method Variable setCode(string $value)
 * @method string getName()
 * @method Variable setName(string $value)
 *
 * @api
 * @since 100.0.2
 */
class Variable extends AbstractModel
{
    /**
     * Variable value types.
     */
    public const TYPE_TEXT = 'text';
    public const TYPE_HTML = 'html';

    /**
     * @var int
     */
    protected $_storeId = 0;

    /**
     * @var Escaper
     */
    protected $_escaper = null;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Escaper $escaper
     * @param ResourceModel\Variable $resource
     * @param AbstractDb $resourceCollection
     * @param array $data
     * @param WYSIWYGValidatorInterface|null $wysiwygValidator
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Escaper $escaper,
        ResourceModel\Variable $resource,
        AbstractDb $resourceCollection = null,
        array $data = [],
        private ?WYSIWYGValidatorInterface $wysiwygValidator = null
    ) {
        $this->_escaper = $escaper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);

        $this->wysiwygValidator = $wysiwygValidator
            ?? ObjectManager::getInstance()->get(WYSIWYGValidatorInterface::class);
    }

    /**
     * Internal Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(ResourceModel\Variable::class);
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
        $this->_storeId = $storeId;
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
        return $this->_storeId;
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
                $value = nl2br((string)$this->_escaper->escapeHtml($value));
            }
            return $value;
        }
        return $this->getData('html_value');
    }

    /**
     * @inheritDoc
     */
    public function beforeSave()
    {
        $html_field = $this->getValue(self::TYPE_HTML);
        parent::beforeSave();

        //Validating HTML content.
        if ($html_field && $html_field !== $this->getOrigData('html_value')) {
            $this->wysiwygValidator->validate($html_field);
        }
        return $this;
    }

    /**
     * Validation of object data. Checking for unique variable code
     *
     * @return Phrase|bool
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
     * Retrieve variables option array.
     *
     * @todo: extract method as separate class
     * @param bool $withGroup
     * @return array
     */
    public function getVariablesOptionArray($withGroup = false)
    {
        /* @var $collection Collection */
        $collection = $this->getCollection();
        $variables = [];
        foreach ($collection->toOptionArray() as $variable) {
            $variables[] = [
                'value' => '{{customVar code=' . $variable['value'] . '}}',
                'label' => __('%1', $this->_escaper->escapeHtml($variable['label'])),
            ];
        }
        if ($withGroup && $variables) {
            $variables = [['label' => __('Custom Variables'), 'value' => $variables]];
        }
        return $variables;
    }
}
