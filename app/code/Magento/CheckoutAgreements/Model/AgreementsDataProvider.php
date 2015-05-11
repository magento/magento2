<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CheckoutAgreements\Model;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * Data provider for "Terms and Conditions" form.
 */
class AgreementsDataProvider implements DataProviderInterface
{
    /**
     * @var array
     */
    private $meta = [];

    /**
     * @var \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface
     */
    protected $checkoutAgreementsRepository;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;

    /**
     * @param \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository
     * @param \Magento\Framework\Escaper $escaper
     */
    public function __construct(
        \Magento\CheckoutAgreements\Api\CheckoutAgreementsRepositoryInterface $checkoutAgreementsRepository,
        \Magento\Framework\Escaper $escaper
    ) {
        $this->checkoutAgreementsRepository = $checkoutAgreementsRepository;
        $this->escaper = $escaper;
    }

    /**
     * Get meta data
     *
     * @return array
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * Get fields meta info
     *
     * @param string $fieldSetName
     * @return array
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return isset($this->meta[$fieldSetName]['fields']) ? $this->meta[$fieldSetName]['fields'] : [];
    }

    /**
     * Get field meta info
     *
     * @param string $fieldSetName
     * @param string $fieldName
     * @return array
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return isset($this->meta[$fieldSetName]['fields'][$fieldName])
            ? $this->meta[$fieldSetName]['fields'][$fieldName]
            : [];
    }

    /**
     * Get config data
     *
     * @return mixed
     */
    public function getConfigData()
    {
        return [];
    }

    /**
     * Set data
     *
     * @param mixed $config
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setConfigData($config)
    {
        // do nothing
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        $form = [];
        $agreementsList = $this->checkoutAgreementsRepository->getList();
        foreach ($agreementsList as $agreement) {
            $name = $agreement->getAgreementId();
            $form[$name] = [
                'component' => 'Magento_Ui/js/form/element/abstract',
                'config' => [
                    'customScope' => 'checkoutAgreements',
                    'customEntry' => 'checkoutAgreements.' . $name,
                    'template' => 'Magento_CheckoutAgreements/form/element/agreement'
                ],
                'agreementConfiguration' => [
                    'content' => $agreement->getIsHtml()
                        ? $agreement->getContent()
                        : nl2br($this->escaper->escapeHtml($agreement->getContent())),
                    'height' => $agreement->getContentHeight(),
                    'checkboxText' => $agreement->getCheckboxText()
                ],
                'dataScope' => $name,
                'provider' => 'checkoutProvider',
                'validation' => ['checked' => true],
                'customEntry' => null,
                'visible' => true
            ];
        }
        $result['components']['checkout']['children']['steps']['children']['review']['children']
        ['beforePlaceOrder']['children']['checkoutAgreements']['children'] = $form;
        return $result;
    }

    /**
     * Get field name in request
     *
     * @return string
     */
    public function getRequestFieldName()
    {
        return null;
    }

    /**
     * Get primary field name
     *
     * @return string
     */
    public function getPrimaryFieldName()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function addFilter($field, $condition = null)
    {
        // do nothing
    }

    /**
     * Add field to select
     *
     * @param string|array $field
     * @param string|null $alias
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addField($field, $alias = null)
    {
        // do nothing
    }

    /**
     * self::setOrder() alias
     *
     * @param string $field
     * @param string $direction
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addOrder($field, $direction)
    {
        // do nothing
    }

    /**
     * Set Query limit
     *
     * @param int $offset
     * @param int $size
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setLimit($offset, $size)
    {
        // do nothing
    }

    /**
     * Removes field from select
     *
     * @param string|null $field
     * @param bool $isAlias Alias identifier
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeField($field, $isAlias = false)
    {
        // do nothing
    }

    /**
     * Removes all fields from select
     *
     * @return void
     */
    public function removeAllFields()
    {
        // do nothing
    }

    /**
     * Retrieve count of loaded items
     *
     * @return int
     */
    public function count()
    {
        return 0;
    }
}
