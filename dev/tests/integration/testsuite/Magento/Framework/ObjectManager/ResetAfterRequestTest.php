<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\ObjectManager;

use Magento\Framework\App\Utility\Classes;
use Magento\Framework\Exception\RuntimeException;
use Magento\Framework\ObjectManager\FactoryInterface as ObjectManagerFactoryInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\TestFramework\ApplicationStateComparator\Collector;
use Magento\Framework\TestFramework\ApplicationStateComparator\Comparator;
use Magento\Framework\TestFramework\ApplicationStateComparator\CompareType;

/**
 * Test that verifies that resetState method for classes cause the state to be the same as it was initially constructed
 * @magentoDbIsolation disabled
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class ResetAfterRequestTest extends \PHPUnit\Framework\TestCase
{

    /**
     * @var ObjectManagerInterface
     */
    private ?ObjectManagerInterface $objectManager;
    /**
     * @var Comparator
     */
    private ?Comparator $comparator;
    /**
     * @var Collector
     */
    private ?Collector $collector;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->comparator = $this->objectManager->create(Comparator::class);
        $this->collector = $this->objectManager->create(Collector::class);
    }

    /**
     * Provides list of all classes and virtual classes that implement ResetAfterRequestInterface
     *
     * @return array
     * @magentoAppIsolation enabled
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function resetAfterRequestClassDataProvider()
    {
        $resetAfterRequestClasses = [];
        foreach (Classes::getVirtualClasses() as $name => $type) {
            try {
                if (!class_exists($type)) {
                    continue;
                }
                if (is_a($type, ObjectManagerInterface::class)) {
                    continue;
                }
                if (is_a($type, ObjectManagerFactoryInterface::class)) {
                    continue;
                }
                if (is_a($type, ResetAfterRequestInterface::class, true)) {
                    $resetAfterRequestClasses[] = [$name];
                }
            } catch (\Error $error) {
                continue;
            }
        }
        foreach (array_keys(Classes::collectModuleClasses('[A-Z][a-z\d][A-Za-z\d\\\\]+')) as $type) {
            if (str_contains($type, "_files")) {
                continue; // We have to skip the fixture files that collectModuleClasses returns;
            }
            try {
                if (!class_exists($type)) {
                    continue;
                }
                if (!is_a($type, ResetAfterRequestInterface::class, true)) {
                    continue; // We only want to return classes that implement ResetAfterRequestInterface
                }
                if (is_a($type, ObjectManagerInterface::class, true)) {
                    continue;
                }
                if (is_a($type, ObjectManagerFactoryInterface::class, true)) {
                    continue;
                }
                $reflectionClass = new \ReflectionClass($type);
                if ($reflectionClass->isAbstract()) {
                    continue; // We can't test abstract classes since they can't instantiate.
                }
                if (\Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection::class == $type) {
                    continue; // This class isn't abstract, but it can't be constructed itself without error
                }
                if (\Magento\Eav\Model\ResourceModel\Form\Attribute\Collection::class == $type) {
                    continue; // Note: This class isn't abstract, but it cannot be constructed itself.
                    // It requires subclass to modify protected $_moduleName to be constructed.
                }
                $resetAfterRequestClasses[] = [$type];
            } catch (\Throwable $throwable) {
                continue;
            }
        }
        return $resetAfterRequestClasses;
    }

    /**
     * Verifies that resetState method for classes cause the state to be the same as it was initially constructed
     *
     * @param string $className
     * @dataProvider resetAfterRequestClassDataProvider
     * @magentoAppArea graphql
     * @magentoDbIsolation disabled
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function testResetAfterRequestClasses(string $className)
    {
        if (\Magento\Backend\Model\Locale\Resolver::class == $className) {  // FIXME: ACPT-1369
            static::markTestSkipped(
                "FIXME: Temporal coupling with Magento\Backend\Model\Locale\Resolver and its _request"
            );
        }
        try {
            $object = $this->objectManager->create($className);
        } catch (\BadMethodCallException $exception) {
            static::markTestSkipped(sprintf(
                'The class "%s" cannot be be constructed without proper arguments %s',
                $className,
                (string)$exception
            ));
        } catch (\ReflectionException $reflectionException) {
            static::markTestSkipped(sprintf(
                'The class "%s" cannot be constructed.  It may require different area. %s',
                $className,
                (string)$reflectionException
            ));
        } catch (\Error $error) {
            static::markTestSkipped(sprintf(
                'The class "%s" cannot be constructed.  It had Error. %s',
                $className,
                (string)$error
            ));
        } catch (RuntimeException $exception) {
            // TODO: We should find a way to test these classes that require additional run time data/configuration
            static::markTestSkipped(sprintf(
                'The class "%s" had RuntimeException. %s',
                $className,
                (string)$exception
            ));
        } catch (\Throwable $throwable) {
            throw new \Exception(
                sprintf("testResetAfterRequestClasses failed on %s", $className),
                0,
                $throwable
            );
        }
        try {
            /** @var ResetAfterRequestInterface $object */
            $beforeProperties = $this->collector->getPropertiesFromObject(
                $object,
                CompareType::COMPARE_BETWEEN_REQUESTS
            );
            $object->_resetState();
            $afterProperties = $this->collector->getPropertiesFromObject(
                $object,
                CompareType::COMPARE_BETWEEN_REQUESTS
            );
            $differences = [];
            foreach ($afterProperties as $propertyName => $propertyValue) {
                if ($propertyValue instanceof ObjectManagerInterface) {
                    continue; // We need to skip ObjectManagers
                }
                if ($propertyValue instanceof \Magento\Framework\Model\ResourceModel\Db\AbstractDb) {
                    continue; // The _tables array gets added to
                }
                if ($propertyValue instanceof \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot) {
                    continue;
                }
                if ('pluginList' == $propertyName) {
                    continue; // We can skip plugin List loading from intercepters.
                }
                if ('_select' == $propertyName) {
                    continue; // We can skip _select because we load a fresh new Select after reset
                }
                if ('_regionModels' == $propertyName
                    && is_a($className, \Magento\Customer\Model\Address\AbstractAddress::class, true)) {
                    continue; // AbstractAddress has static property _regionModels, so it would fail this test.
                    // TODO: Can we convert _regionModels to member variable,
                    // or move to a dependency injected service class instead?
                }
                $result = $this->comparator->checkValues(
                    $beforeProperties[$propertyName] ?? null,
                    $propertyValue,
                    3
                );
                if ($result) {
                    $differences[$propertyName] = $result;
                }
            }
            $this->assertEmpty($differences, var_export($differences, true));
        } catch (\Throwable $throwable) {
            throw new \Exception(
                sprintf("testResetAfterRequestClasses failed on %s", $className),
                0,
                $throwable
            );
        }
    }
}
