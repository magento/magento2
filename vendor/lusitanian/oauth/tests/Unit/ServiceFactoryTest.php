<?php
/**
 * @category   OAuth
 * @package    Tests
 * @author     David Desberg <david@daviddesberg.com>
 * @author     Chris Heng <bigblah@gmail.com>
 * @author     Pieter Hordijk <info@pieterhordijk.com>
 * @copyright  Copyright (c) 2013 The authors
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 */
namespace OAuth\Unit;

use OAuth\ServiceFactory;

class ServiceFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers OAuth\ServiceFactory::setHttpClient
     */
    public function testSetHttpClient()
    {
        $factory = new ServiceFactory();

        $this->assertInstanceOf(
            '\\OAuth\\ServiceFactory',
            $factory->setHttpClient($this->getMock('\\OAuth\\Common\\Http\\Client\\ClientInterface'))
        );
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     */
    public function testRegisterServiceThrowsExceptionNonExistentClass()
    {
        $this->setExpectedException('\\OAuth\Common\Exception\Exception');

        $factory = new ServiceFactory();
        $factory->registerService('foo', 'bar');
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     */
    public function testRegisterServiceThrowsExceptionWithClassIncorrectImplementation()
    {
        $this->setExpectedException('\\OAuth\Common\Exception\Exception');

        $factory = new ServiceFactory();
        $factory->registerService('foo', 'OAuth\\ServiceFactory');
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     */
    public function testRegisterServiceSuccessOAuth1()
    {
        $factory = new ServiceFactory();

        $this->assertInstanceOf(
            '\\OAuth\\ServiceFactory',
            $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake')
        );
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     */
    public function testRegisterServiceSuccessOAuth2()
    {
        $factory = new ServiceFactory();

        $this->assertInstanceOf(
            '\\OAuth\\ServiceFactory',
            $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake')
        );
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV1Service
     */
    public function testCreateServiceOAuth1NonRegistered()
    {
        $factory = new ServiceFactory();

        $service = $factory->createService(
            'twitter',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth1\\Service\\Twitter', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV1Service
     */
    public function testCreateServiceOAuth1Registered()
    {
        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\OAuth1\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV1Service
     */
    public function testCreateServiceOAuth1RegisteredAndNonRegisteredSameName()
    {
        $factory = new ServiceFactory();

        $factory->registerService('twitter', '\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake');

        $service = $factory->createService(
            'twitter',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\OAuth1\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServiceOAuth2NonRegistered()
    {
        $factory = new ServiceFactory();

        $service = $factory->createService(
            'facebook',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\\OAuth2\\Service\\Facebook', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServiceOAuth2Registered()
    {
        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\OAuth2\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServiceOAuth2RegisteredAndNonRegisteredSameName()
    {
        $factory = new ServiceFactory();

        $factory->registerService('facebook', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake');

        $service = $factory->createService(
            'facebook',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\OAuth2\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV1Service
     */
    public function testCreateServiceThrowsExceptionOnPassingScopesToV1Service()
    {
        $this->setExpectedException('\\OAuth\Common\Exception\Exception');

        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array('bar')
        );
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     */
    public function testCreateServiceNonExistentService()
    {
        $factory = new ServiceFactory();

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertNull($service);
    }

    /**
     * @covers OAuth\ServiceFactory::registerService
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServicePrefersOauth2()
    {
        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth1\\Service\\Fake');
        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface')
        );

        $this->assertInstanceOf('\\OAuth\OAuth2\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServiceOAuth2RegisteredWithClassConstantsAsScope()
    {
        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array('FOO')
        );

        $this->assertInstanceOf('\\OAuth\OAuth2\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake', $service);
    }

    /**
     * @covers OAuth\ServiceFactory::createService
     * @covers OAuth\ServiceFactory::getFullyQualifiedServiceName
     * @covers OAuth\ServiceFactory::buildV2Service
     * @covers OAuth\ServiceFactory::resolveScopes
     */
    public function testCreateServiceOAuth2RegisteredWithCustomScope()
    {
        $factory = new ServiceFactory();

        $factory->registerService('foo', '\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake');

        $service = $factory->createService(
            'foo',
            $this->getMock('\\OAuth\\Common\\Consumer\\CredentialsInterface'),
            $this->getMock('\\OAuth\\Common\\Storage\\TokenStorageInterface'),
            array('custom')
        );

        $this->assertInstanceOf('\\OAuth\OAuth2\Service\\ServiceInterface', $service);
        $this->assertInstanceOf('\\OAuthTest\\Mocks\\OAuth2\\Service\\Fake', $service);
    }
}
