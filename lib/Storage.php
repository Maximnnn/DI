<?php


namespace DI;


class Storage
{
    private $storage    = [];
    private $singletons = [];

    public function factory(string $class, $method)
    {
        if ($class === $method)
            return;

        $this->storage[$class] = $method;
    }

    public function single(string $class, $method)
    {
        $this->singletons[] = $class;

        if ($class === $method)
            return;

        $this->storage[$class] = $method;
    }

    public function get(string $class)
    {
        return $this->storage[$class] ?? null;
    }

    public function exists(string $class): bool
    {
        return in_array($class, $this->storage,  true);
    }

    public function isSingleton(string $class): bool
    {
        return in_array($class, $this->singletons, true);
    }
}
