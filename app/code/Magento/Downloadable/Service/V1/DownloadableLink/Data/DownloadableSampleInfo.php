<?php
/**
 *
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
namespace Magento\Downloadable\Service\V1\DownloadableLink\Data;

use \Magento\Framework\Service\Data\AbstractExtensibleObject;

/**
 * @codeCoverageIgnore
 */
class DownloadableSampleInfo extends AbstractExtensibleObject
{
    const ID = 'id';

    const TITLE = 'title';

    const SORT_ORDER = 'sort_order';

    const SAMPLE_RESOURCE = 'sample_resource';

    /**
     * Product sample id
     *
     * @return int|null Sample(or link) id
     */
    public function getId()
    {
        return $this->_get(self::ID);
    }

    /**
     * Sample title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->_get(self::TITLE);
    }

    /**
     * File or URL of sample
     *
     * @return \Magento\Downloadable\Service\V1\DownloadableLink\Data\DownloadableResourceInfo
     */
    public function getSampleResource()
    {
        return $this->_get(self::SAMPLE_RESOURCE);
    }

    /**
     * Sort order index for sample
     *
     * @return int
     */
    public function getSortOrder()
    {
        return (int)$this->_get(self::SORT_ORDER);
    }
}
