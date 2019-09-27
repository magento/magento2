<?php


namespace Magento\UrlRewrite\Test\Unit\Provider;

use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

use Magento\Framework\App\ResourceConnection;
use Magento\UrlRewrite\Provider\RequestPathProviderInterface;
use Magento\UrlRewrite\Provider\RequestPathProvider;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;

class RequestPathProviderTest extends TestCase
{
    /** @var RequestPathProviderInterface */
    private $provider;

    /** @var MockObject|ResourceConnection */
    private $resourceConnection;

    /** @var MockObject|AdapterInterface */
    private $adapter;

    /** @var MockObject|Select */
    private $select;

    /** {@inheritDoc} */
    public function setUp()
    {
        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->adapter = $this->createMock(AdapterInterface::class);
        $this->select = $this->createMock(Select::class);

        $this->provider = new RequestPathProvider($this->resourceConnection);
    }

    /** {@inheritDoc} */
    public function tearDown()
    {
        unset($this->provider);
        parent::tearDown();
    }

    /**
     * @param string      $targetPath
     * @param array       $returnedResult
     * @param null|string $expectedResult
     */
    public function testGetRequestPath($targetPath, $returnedResult, $expectedResult)
    {
        $this->getConfiguredMocks($returnedResult);

        $result = $this->provider->getRequestPath($targetPath);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     *  mixed[]
     */
    public function getRequestPathProvider()
    {
        return [
            'founded' => [
                'targetPath' => 'catalog/category/view/9',
                'returnedResult' => [ 'request_path' => 'tops/test.html'],
                'expectedResult' => 'tops/test.html'
            ],
            'notFound' => [
                'targetPath' => 'catalog/category/view/1',
                'returnedResult' => [],
                'expectedResult' => null
            ]
        ];
    }

    private function getConfiguredMocks($returnedResult)
    {
        $this->resourceConnection
            ->expects($this->once())
            ->method('getConnection')
            ->will($this->adapter);

        $this->adapter
            ->expects($this->once())
            ->method('select')
            ->willReturn($this->select);

        $this->adapter
            ->expects($this->once())
            ->method('fetchRow')
            ->willReturn($returnedResult);

        $this->adapter->expects($this->once())->method('quoteIdentifier');

        $this->select->expects($this->once())->method('from');
        $this->select->expects($this->once())->method('where');
    }
}
