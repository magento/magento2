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

namespace Magento\Catalog\Test\Fixture\CatalogProductSimple;

use Mtf\Fixture\FixtureInterface;
use Mtf\Fixture\FixtureFactory;
use Magento\Catalog\Test\Fixture\CatalogAttributeSet;

/**
 * Class AttributeSetId
 *
 *  Data keys:
 *  - dataSet
 *  - attribute_set
 */
class AttributeSetId implements FixtureInterface
{
    /**
     * Attribute Set name
     *
     * @var string
     */
    protected $data;

    /**
     * Attribute Set fixture
     *
     * @var CatalogAttributeSet
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
            $attributeSet = $fixtureFactory->createByCode('catalogAttributeSet', ['dataSet' => $data['dataSet']]);
        }
        if (isset($data['attribute_set']) && $data['attribute_set'] instanceof CatalogAttributeSet) {
            $attributeSet = $data['attribute_set'];
        }
        /** @var CatalogAttributeSet $attributeSet */
        $this->data = $attributeSet->getAttributeSetName();
        $this->attributeSet = $attributeSet;
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
     * Return Attribute Set fixture
     *
     * @return CatalogAttributeSet
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
