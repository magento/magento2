<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldBackendUi\Plugin;

use Magento\Customer\Model\Customer\DataProviderWithDefaultAddresses;
use Magento\Framework\App\Request\Http;
use Mod\HelloWorldApi\Api\ExtraAbilitiesProviderInterface;
use Magento\Framework\Api\ExtensibleDataObjectConverter;

/**
 * Get is allow add description class.
 */
class GetIsAllowAddDescriptionPlugin
{
    /**
     * @var Http
     */
    private $request;

    /**
     * @var ExtraAbilitiesProviderInterface
     */
    private $extraAbilitiesProvider;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $dataObjectConverter;

    /**
     * Plugin constructor.
     *
     * @param Http $request
     * @param ExtraAbilitiesProviderInterface $extraAbilitiesProvider
     * @param ExtensibleDataObjectConverter $dataObjectConverter
     */
    public function __construct(
        Http $request,
        ExtraAbilitiesProviderInterface $extraAbilitiesProvider,
        ExtensibleDataObjectConverter $dataObjectConverter
    ) {
        $this->request = $request;
        $this->extraAbilitiesProvider = $extraAbilitiesProvider;
        $this->dataObjectConverter = $dataObjectConverter;
    }

    /**
     * Adds extra abilities data in array.
     *
     * @param DataProviderWithDefaultAddresses $subject
     * @param array $data
     * @return array
     */
    public function afterGetData(DataProviderWithDefaultAddresses $subject, array $data)
    {
        $customerId = (int)$this->request->getParam('id');
        $customerExtraAttributesObject = $this->extraAbilitiesProvider->getExtraAbilities($customerId);
        if (!empty($customerExtraAttributesObject)) {
            $customerExtraAttributes = $this->dataObjectConverter->toFlatArray($customerExtraAttributesObject[0], []);
            if ($customerExtraAttributes['is_allowed_add_description'] == 1) {
                $data[$customerId]['customer']['is_allowed_add_description'] = '1';
                return $data;
            }
        }
        return $data;
    }
}
