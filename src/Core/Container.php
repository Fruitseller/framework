<?php

namespace framework\Core;

use ReflectionClass;
use ArrayAccess;

class Container implements ArrayAccess {

	protected $bindings = [];
	protected $instances = [];

	public function bind($key, $value, $singleton = false) {

		$this->bindings[$key] = compact('value', 'singleton');
	}

	public function singleton($key, $value) {
		return $this->bind($key, $value, true);
	}

	public function getBinding($key) {

		if (!array_key_exists($key, $this->bindings)) {
			return null;
		}

		return $this->bindings[$key];
	}

	public function isSingleton($key) {
		$binding = $this->getBinding($key);

		if ($binding === null) {
			return false;
		}

		return $binding['singleton'];
	}

	public function singletonResolved($key) {
		return array_key_exists($key, $this->instances);
	}

	public function getSingletonInstance($key) {
		return $this->singletonResolved($key) ? $this->instances[$key] : null;
	}

	public function resolve($key, array $args = array()) {

		$class = $this->getBinding($key);

		if ($class === null) {
			$class = $key;
		}

		if ($this->isSingleton($key) && $this->singletonResolved($key)) {
			return $this->getSingletonInstance($key);
		}

		$object = $this->buildObject($class, $args);

		return $this->prepareObject($key, $object);
	}

	protected function prepareObject($key, $object) {
		if ($this->isSingleton($key)) {
			$this->instances[$key] = $object;
		}

		return $object;
	}

	protected function buildObject($class, array $args = array()) {

		$className = $class['value'];

		$reflector = new ReflectionClass($className);

		if (!$reflector->isInstantiable()) {
			throw new ClassIsNotInstantiableException('Class [$classname] is not a resolvable dependency');
		}

		if ($reflector->getConstructor() !== null) {

			$constructor = $reflector->getConstructor();
			$dependencies = $constructor->getParameters();

			$args = $this->buildDependencies($args, $dependencies, $class);
		}

		$object = $reflector->newInstanceArgs($args);

		return $object;
	}

	protected function buildDependencies($args, $dependencies, $class) {
		foreach ($dependencies as $dependency) {

			if ($dependency->isOptional()) continue;
			if ($dependency->isArray()) continue;

			$class = $dependency->getClass();

			if ($class === null) continue;

			if (get_class($this) === $class->name) {
				array_unshift($args, $this);
				continue;
			}

			array_unshift($args, $this->resolve($class->name));
		}

		return $args;
	}

	public function offsetGet($key) {
		return $this->resolve($key);
	}

	public function offsetSet($key, $value) {
		return $this->bind($key, $value);
	}

	public function offsetExists($key) {
		return array_key_exists($key, $this->bindings);
	}

	public function offsetUnset($key) {
		unset($this->bindings[$key]);
	}


}
