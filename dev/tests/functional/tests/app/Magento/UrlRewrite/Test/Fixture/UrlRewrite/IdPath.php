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

namespace Magento\UrlRewrite\Test\Fixture\UrlRewrite;

use Mtf\Fixture\FixtureFactory;
use Mtf\Fixture\FixtureInterface;

/**
 * Class IdPath
 * Prepare ID Path
 */
class IdPath implements FixtureInterface
{
    /**
     * Resource data
     *
     * @var string
     */
    protected $data;

    /**
     * Return category
     *
     * @var FixtureInterface
     */
    protected $entity;

    /**
     * @param FixtureFactory $fixtureFactory
     * @param array $params
     * @param array $data
     */
    public function __construct(FixtureFactory $fixtureFactory, array $params, array $data = [])
    {
        $this->params = $params;
        if (!isset($data['entity'])) {
            $this->data = array_shift($data);
            return;
        }
        preg_match('`%(.*?)%`', $data['entity'], $dataSet);
        $explodeValue = explode('::', $dataSet[1]);
        if (!empty($explodeValue) && count($explodeValue) > 1) {
            /** @var FixtureInterface $fixture */
            $this->entity = $fixtureFactory->createByCode($explodeValue[0], ['entity' => $explodeValue[1]]);
            $this->entity->persist();
            $this->data = preg_replace('`(%.*?%)`', $this->entity->getId(), $data['entity']);
        } else {
            $this->data = strval($data['entity']);
        }
    }

    /**
     * Persist custom selections products
     *
     * @return void
     */
    public function persist()
    {
        //
    }

    /**
     * Return prepared data
     *
     * @param string|null $key
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getData($key = null)
    {
        return $this->data;
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

    /**
     * Return entity
     *
     * @return FixtureInterface
     */
    public function getEntity()
    {
        return $this->entity;
    }
}
