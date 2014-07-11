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

namespace Magento\Catalog\Test\Fixture\CatalogAttributeSet;

use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;

/**
 * Class SkeletonSet
 *
 *  Data keys:
 *  - dataSet
 */
class SkeletonSet implements FixtureInterface
{
    /**
     * Attribute Set name
     *
     * @var string
     */
    protected $data;

    /**
     * New Attribute Set
     *
     * @var array
     */
    protected $attributeSet;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (isset($data['dataSet']) && $data['dataSet'] !== '-') {
            $parentSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataSet' => $data['dataSet']]);
            if (!$parentSet->hasData('attribute_set_id')) {
                $parentSet->persist();
            }
            /** @var CatalogAttributeSet $parentSet */
            $this->data = $parentSet->getAttributeSetName();
            $this->attributeSet = $parentSet;
        }
    }

    /**
     * Persist attribute options
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data set
     *
     * @param string|null $key
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
    }

    /**
     * Get Attribute Set
     *
     * @return array
     */
    public function getAttributeSet()
    {
        return $this->attributeSet;
    }

    /**
     * Return data set configuration settings
     *
     * @return array
     */
    public function getDataConfig()
    {
        return $this->params;
    }
}
