<?php

namespace Zeroplex\Tool\Test;

use PHPUnit\Framework\TestCase;
use Zeroplex\Tool\Container;

class ContainerTest extends TestCase
{
    protected $container = null;

    public function setUp()
    {
        $this->container = new Container();
    }

    public function tearDown()
    {
        $this->container = null;
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testServiceCheckerWithInvalidName()
    {
        $this->container->checkServiceName('');
    }

    public function testIfServiceExists()
    {
        $this->assertSame(false, $this->container->hasService('not-exists'));
    }

    public function testIfProviderExits()
    {
        $this->assertSame(false, $this->container->hasProvider('not-exists'));
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGettingNotExistsService()
    {
        $this->container->get('not-exists');
    }

    public function testRegisterSingletonClosure()
    {
        $this->container->singleton('echo', function () {
            return 'echo';
        });

        $this->assertSame(true, $this->container->hasService('echo'));

        return $this->container;
    }

    /**
     * @depends testRegisterSingletonClosure
     *
     * @expectedException \RuntimeException
     */
    public function testRegisterDuplecatedName($container)
    {
        $container->singleton('echo', function () {
            return;
        });
    }

    /**
     * @depends testRegisterSingletonClosure
     */
    public function testOffsetExists($container)
    {
        $this->assertSame(true, $container->offsetExists('echo'));
    }

    /**
     * @depends testRegisterSingletonClosure
     */
    public function testOffsetGetter($container)
    {
        $this->assertSame('echo', $container['echo']);
    }

    /**
     * @depends testRegisterSingletonClosure
     */
    public function testOffsetUnsetter($container)
    {
        unset($container['echo']);

        $this->assertSame(false, $container->hasService('echo'));
    }

    public function testObjectSingleton()
    {
        $this->container->singleton('date', new \DateTimeImmutable);

        $this->assertSame(true, $this->container->hasService('date'));

        return $this->container;
    }

    /**
     * @depends testObjectSingleton
     */
    public function testObjectSingletonGetter($container)
    {
        $this->assertInstanceOf('\DateTimeImmutable', $container->get('date'));
    }

    public function testSettingProvider()
    {
        $this->container->provide('date', function () {
            return new \DateTimeImmutable;
        });

        $this->assertSame(true, $this->container->hasProvider('date'));

        return $this->container;
    }

    /**
     * @depends testSettingProvider
     */
    public function testProviderGetter($container)
    {
        $out = $container->get('date');
        $this->assertInstanceOf(\DateTimeImmutable::class, $out);
    }

    /**
     * @depends testSettingProvider
     */
    public function testProviderRemover($container)
    {
        $container->remove('date');

        $this->assertFalse($container->hasProvider('date'));
    }

    public function testSettingOffset()
    {
        $this->container['date'] = function () {
            return new \DateTimeImmutable;
        };

        $this->assertTrue($this->container->hasProvider('date'));
    }
}
