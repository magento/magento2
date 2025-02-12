<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Translation\Test\Fixture;

use Magento\Framework\DataObject;
use Magento\Store\Model\Store;
use Magento\TestFramework\Fixture\RevertibleDataFixtureInterface;
use Magento\Translation\Model\ResourceModel\StringUtils;
use Magento\Translation\Model\StringUtilsFactory;

class Translation implements RevertibleDataFixtureInterface
{
    private const DEFAULT_DATA = [
        'string' => null,
        'translate' => null,
        'locale' => null,
        'store_id' => Store::DEFAULT_STORE_ID,
    ];

    /**
     * @var StringUtils
     */
    private StringUtils $translateResourceModel;

    /**
     * @var StringUtilsFactory
     */
    private StringUtilsFactory $translateResourceModelFactory;

    /**
     * @param StringUtils $translateResourceModel
     * @param StringUtilsFactory $translateModelFactory
     */
    public function __construct(
        StringUtils $translateResourceModel,
        StringUtilsFactory $translateModelFactory,
    ) {
        $this->translateResourceModel = $translateResourceModel;
        $this->translateResourceModelFactory = $translateModelFactory;
    }

    /**
     * {@inheritdoc}
     * @param array $data Parameters
     * <pre>
     *    $data = [
     *      'string'    => (string) Text to translate. Required.
     *      'translate' => (string) Translated text. Required.
     *      'locale'    => (string) Locale code. For example: fr_FR. Optional. Default: Current locale
     *      'store_id'  => (int) Store ID. Optional. Default: 0
     *    ]
     * </pre>
     */
    public function apply(array $data = []): ?DataObject
    {
        $data = array_merge(self::DEFAULT_DATA, $data);
        $this->translateResourceModel->saveTranslate(
            $data['string'],
            $data['translate'],
            $data['locale'],
            $data['store_id']
        );

        return $this->translateResourceModelFactory->create(['data' => $data]);
    }

    /**
     * @inheritDoc
     */
    public function revert(DataObject $data): void
    {
        $this->translateResourceModel->deleteTranslate($data->getString(), $data->getLocale(), $data->getStoreId());
    }
}
