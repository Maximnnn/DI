<?php

namespace DI;

class DiContainer
{
    /**
     * @var Storage
     */
    private $storage;

    public function __construct(Storage $storage)
    {
        $this->storage = $storage;
    }

    /**
     * @param string $class
     * @param array $defaultValues
     * @return mixed
     * @throws \ReflectionException
     */
    public function make(string $class, array $defaultValues = [])
    {
        if ($this->storage->exists($class)) {

            $factory = $this->storage->get($class);

            if (is_string($factory)) {
                $object = $this->make($factory);
            } else if (is_callable($factory)) {
                $object = $factory($this);
            } else {
                $object = $factory;
            }

        } else {
            $object = $this->createObjectFromClassName($class, $defaultValues);
        }

        if ($this->storage->isSingleton($class)) {
            $this->storage->factory($class, $object);
        }

        return $object;
    }

    /**
     * @param string $className
     * @param array
     * $defaultValues
     * @return mixed
     * @throws \ReflectionException
     */
    private function createObjectFromClassName(string $className, array $defaultValues = [])
    {
        $reflection = new \ReflectionClass($className);

        if (is_null($constructor = $reflection->getConstructor())) {
            return new $className;
        }

        return new $className(...$this->getParameters($constructor, $defaultValues));
    }

    /**
     * @param \ReflectionMethod $method
     * @param array $defaultValues
     * @return array
     * @throws \ReflectionException
     */
    private function getParameters(\ReflectionMethod $method, array $defaultValues = [])
    {
        $parameters = [];

        foreach ($method->getParameters() as $parameter) {

            if (isset($defaultValues[$parameter->getName()])) {
                $parameters[] = $defaultValues[$parameter->getName()];
            } else if (!is_null($parameter->getClass())) {
                $parameters[] = $this->createObjectFromClassName($parameter->getClass()->getName());
            } else if ($parameter->getDefaultValue()) {
                $parameters[] = $parameter->getDefaultValue();
            }
        }

        return $parameters;
    }
}