<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

class Validator implements ValidatorInterface
{
    /**
     * @var ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @param ConfigInterface $postCodesConfig
     */
    public function __construct(\Magento\Directory\Model\Country\Postcode\ConfigInterface $postCodesConfig)
    {
        $this->postCodesConfig = $postCodesConfig;
    }

    /**
     * {@inheritdoc}
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
