<?php

declare(strict_types=1);

namespace LizardsAndPumpkins\DataPool\KeyValueStore\Memcached;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\KeyValueStore\KeyValueStore;

class MemcachedKeyValueStore implements KeyValueStore
{
    const MEMCACHED_RES_NOTFOUND = 16;

    /**
     * @var \Memcached
     */
    private $client;

    public function __construct(\Memcached $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, $value)
    {
        $this->client->set($key, $value);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        $value = $this->client->get($key);

        if ($this->client->getResultCode() === self::MEMCACHED_RES_NOTFOUND) {
            throw new KeyNotFoundException(sprintf('Key not found "%s"', $key));
        }

        return $value;
    }

    public function has(string $key) : bool
    {
        $this->client->get($key);
        return $this->client->getResultCode() !== self::MEMCACHED_RES_NOTFOUND;
    }

    /**
     * @param string[] $keys
     * @return mixed[]
     */
    public function multiGet(string ...$keys) : array
    {
        if (count($keys) === 0) {
            return [];
        }

        return $this->client->getMulti($keys);
    }

    /**
     * @param mixed[] $items
     */
    public function multiSet(array $items)
    {
        $this->client->setMulti($items);
    }
}
