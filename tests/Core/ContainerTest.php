<?php

require 'vendor/autoload.php';

class ContainerTest extends PHPUnit_Framework_TestCase {

	protected $container;

	public function setUp() {
		$this->container = new framework\Core\Container();
	}

	public function testBindingObjectWorks() {
		
		$this->container->bind('foo', 'Bar');

		$this->assertEquals('Bar', $this->container->getBinding('foo'));
	}

	public function testgetBindingsReturnNullWhenNotFound() {
		
		$binding = $this->container->getBinding('bar');

		$this->assertNull($binding);
	}

	public function testResolveClassReturnsObject() {

		$object = $this->container->resolve('Bar');

		$this->assertInstanceOf('Bar', $object);
	}

	public function testArrayAccessSuccess() {

		$this->container['baz'] = 'Bar';
		$object = $this->container['baz'];

		$this->assertInstanceOf('Bar', $object);
	}

}

class Foo {}

class Bar {
	public function __construct(Foo $foo) {}
}

