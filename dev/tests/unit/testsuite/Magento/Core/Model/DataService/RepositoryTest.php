<?php
/**
 * \Magento\Core\Model\DataService\Repository
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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\DataService;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\DataService\Repository
     */
    protected $_repository;

    protected function setUp()
    {
        $this->_repository = new \Magento\Core\Model\DataService\Repository();
    }

    public function testAddGet()
    {
        $data = array();
        $serviceName = 'service_name';
        $this->assertEquals($data, $this->_repository->add($serviceName, $data)->get($serviceName));
    }

    public function testGet()
    {
        $this->assertEquals(null, $this->_repository->get('unknown_service_name'));
    }

    public function testGetByNamespace()
    {
        $result = $this->_repository->getByNamespace('unknown_namespace');
        $this->assertEquals(array(), $result);
    }

    public function testAddGetNamespace()
    {
        $data = array();
        $alias = 'alias';
        $namespace = 'namespace';
        $serviceName = 'service_name';
        $namespaceResults = $this->_repository->add($serviceName, $data)
            ->setAlias($namespace, $serviceName, $alias)
            ->getByNamespace($namespace);
        $this->assertEquals($data, $namespaceResults[$alias]);
    }

    public function testAddGetNamespaceAgain()
    {
        $data = array();
        $alias = 'alias';
        $namespace = 'namespace';
        $serviceName = 'service_name';
        $namespaceResults = $this->_repository->add($serviceName, $data)
            ->setAlias($namespace, $serviceName, 'something_different')
            ->setAlias($namespace, $serviceName, $alias)
            ->getByNamespace($namespace);
        $this->assertEquals($data, $namespaceResults[$alias]);
    }

    public function testGetChild()
    {
        $data = array();
        $serviceName = 'service_name';
        $this->_repository->add($serviceName, $data);
        $this->assertEquals($data, $this->_repository->getChildNode($serviceName));
    }
}
