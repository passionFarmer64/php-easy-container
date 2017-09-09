<?php

declare(strict_types=1);

namespace Zeroplex;

class Container implements \ArrayAccess
{
    protected $services = [];

    protected $providers = [];

    public function __construct()
    {
        $this->services = [];
        $this->providers = [];
    }

    /**
     * singleton instance/service register
     *
     * @param string $name service name
     * @param mixed $service object or closure
     *
     */
    public function singleton(string $name, $service): void
    {
        $this->checkServiceName($name);

        $this->checkDuplicatedName($name);

        $this->services[$name] = $service;
    }

    /**
     * @throws \InvalidArgumentException if service name is invalid
     */
    public function checkServiceName(string $name)
    {
        if (empty($name)) {
            throw new \InvalidArgumentException('service name can not be empty');
        }
    }

    /**
     * @throws \RuntimeException if service or provider name is duplicated
     */
    public function checkDuplicatedName($name)
    {
        if ($this->hasService($name) || $this->hasProvider($name)) {
            throw new \RuntimeException('service or provider is already exists');
        }
    }

    /**
     * register providers
     *
     * @param string $name service name
     * @param \Closure $provider closure container service provider
     *
     * @throws \InvalidArgumentException service name is invalid
     * @throws \RuntimeException service name is wrong
     */
    public function provide(string $name, \Closure $provider): void
    {
        $this->checkServiceName($name);
        $this->checkDuplicatedName($name);

        $this->providers[$name] = $provider;
    }

    /**
     * remove service
     *
     * @param string $name service name
     * @throws \RuntimeException if service name is not found
     */
    public function remove($name): void
    {
        $this->removeService($name);
        $this->removeProvider($name);
    }

    public function removeService($name): void
    {
        if (array_key_exists($name, $this->services)) {
            unset($this->services[$name]);
        }
    }

    public function removeProvider($name)
    {
        if (array_key_exists($name, $this->providers)) {
            unset($this->providers[$name]);
        }
    }

    /**
     * Get service or provider
     *
     * @param string $name service or provider name
     * @return mixed service or provider instance
     *
     * @throws \RuntimeException if service or provider name is not found
     */
    public function get(string $name)
    {
        if ($this->hasService($name)) {
            return $this->getSingletonService($name);
        }

        if ($this->hasProvider($name)) {
            return $this->providers[$name]();
        }

        throw new \RuntimeException('service or provider is not exists');
    }

    public function getSingletonService($name)
    {
        if (!$this->services[$name] instanceof \Closure) {
            return $this->services[$name];
        }

        $instance = $this->services[$name]();
        $this->services[$name] = $instance;
        return $this->services[$name];
    }

    /**
     * Check if service exists
     *
     * @param string $name service name
     * @return bool true if service found, false if not
     */
    public function hasService($name): bool
    {
        return array_key_exists($name, $this->services);
    }

    public function hasProvider($name): bool
    {
        return array_key_exists($name, $this->providers);
    }

    /**
     * Alias of serviceExists()
     *
     * @see \ArrayAccess
     */
    public function offsetExists($offset)
    {
        return $this->hasService($offset) || $this->hasProvider($offset);
    }

    /**
     * Alias of get()
     *
     * @see \ArrayAccess
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Alias of provide()
     *
     * @see \ArrayAccess
     */
    public function offsetSet($offset, $value)
    {
        $this->provide($offset, $value);
    }

    /**
     * Alias of removeService()
     *
     * @see \ArrayAccess
     */
    public function offsetUnset($offset)
    {
        $this->removeService($offset);
        $this->removeProvider($offset);
    }
}
