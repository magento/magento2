<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

/**
 * Class \Magento\Directory\Model\Country\Postcode\Validator
 *
 * @since 2.0.0
 */
class Validator implements ValidatorInterface
{
    /**
     * @var ConfigInterface
     * @since 2.0.0
     */
    protected $postCodesConfig;

    /**
     * @param ConfigInterface $postCodesConfig
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig)
    {
        $this->postCodesConfig = $postCodesConfig;
    }

    /**
     * {@inheritdoc}
     * @since 2.0.0
     */
    public function validate($postcode, $countryId)
    {
        $postCodes = $this->postCodesConfig->getPostCodes();
        if (isset($postCodes[$countryId]) && is_array($postCodes[$countryId])) {
            $patterns = $postCodes[$countryId];
            foreach ($patterns as $pattern) {
                preg_match('/' . $pattern['pattern'] . '/', $postcode, $matches);
                if (count($matches)) {
                    return true;
                }
            }
            return false;
        }
        throw new \InvalidArgumentException('Provided countryId does not exist.');
    }
}
