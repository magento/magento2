<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Ui\Component\MassAction\Group;

use Magento\Framework\Phrase;
use Magento\Framework\UrlInterface;
use Zend\Stdlib\JsonSerializable;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory;

/**
 * Class Options
 * @since 2.0.0
 */
class Options implements JsonSerializable
{
    /**
     * @var array
     * @since 2.0.0
     */
    protected $options;

    /**
     * @var CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * Additional options params
     *
     * @var array
     * @since 2.0.0
     */
    protected $data;

    /**
     * @var UrlInterface
     * @since 2.0.0
     */
    protected $urlBuilder;

    /**
     * Base URL for subactions
     *
     * @var string
     * @since 2.0.0
     */
    protected $urlPath;

    /**
     * Param name for subactions
     *
     * @var string
     * @since 2.0.0
     */
    protected $paramName;

    /**
     * Additional params for subactions
     *
     * @var array
     * @since 2.0.0
     */
    protected $additionalData = [];

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlBuilder
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        UrlInterface $urlBuilder,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->data = $data;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get action options
     *
     * @return array
     * @since 2.0.0
     */
    public function jsonSerialize()
    {
        if ($this->options === null) {
            $options = $this->collectionFactory->create()->setRealGroupsFilter()->toOptionArray();
            $this->prepareData();
            foreach ($options as $optionCode) {
                $this->options[$optionCode['value']] = [
                    'type' => 'customer_group_' . $optionCode['value'],
                    'label' => __($optionCode['label']),
                ];

                if ($this->urlPath && $this->paramName) {
                    $this->options[$optionCode['value']]['url'] = $this->urlBuilder->getUrl(
                        $this->urlPath,
                        [$this->paramName => $optionCode['value']]
                    );
                }

                $this->options[$optionCode['value']] = array_merge_recursive(
                    $this->options[$optionCode['value']],
                    $this->additionalData
                );
            }

            $this->options = array_values($this->options);
        }

        return $this->options;
    }

    /**
     * Prepare addition data for subactions
     *
     * @return void
     * @since 2.0.0
     */
    protected function prepareData()
    {
        foreach ($this->data as $key => $value) {
            switch ($key) {
                case 'urlPath':
                    $this->urlPath = $value;
                    break;
                case 'paramName':
                    $this->paramName = $value;
                    break;
                case 'confirm':
                    foreach ($value as $messageName => $message) {
                        $this->additionalData[$key][$messageName] = (string) new Phrase($message);
                    }
                    break;
                default:
                    $this->additionalData[$key] = $value;
                    break;
            }
        }
    }
}
