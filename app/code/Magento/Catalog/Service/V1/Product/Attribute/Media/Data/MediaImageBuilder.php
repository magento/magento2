<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Catalog\Service\V1\Product\Attribute\Media\Data;

/**
 * Builder for media_image
 *
 * @codeCoverageIgnore
 */
class MediaImageBuilder extends \Magento\Framework\Service\Data\AbstractExtensibleObjectBuilder
{
    /**
     * Set attribute code
     *
     * @param string $code
     * @return $this
     */
    public function setCode($code)
    {
        return $this->_set(MediaImage::CODE, $code);
    }

    /**
     * Set attribute frontend label
     *
     * @param string $label
     * @return $this
     */
    public function setFrontendLabel($label)
    {
        return $this->_set(MediaImage::LABEL, $label);
    }

    /**
     * Set attribute scope. Valid values are 'Global', 'Website' and 'Store View'
     *
     * @param string $scope
     * @return $this
     */
    public function setScope($scope)
    {
        return $this->_set(MediaImage::SCOPE, $scope);
    }

    /**
     * Set true for user attributes or false for system attributes
     *
     * @param bool $isUserDefined
     * @return $this
     */
    public function setIsUserDefined($isUserDefined)
    {
        return $this->_set(MediaImage::IS_USER_DEFINED, $isUserDefined);
    }
}
