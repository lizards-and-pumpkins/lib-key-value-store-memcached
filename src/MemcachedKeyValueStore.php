<?php

namespace LizardsAndPumpkins\DataPool\KeyValue\Memcached;

use LizardsAndPumpkins\DataPool\KeyValue\KeyValueStore;
use LizardsAndPumpkins\DataPool\KeyValue\KeyNotFoundException;

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
	public function set($key, $value)
	{
		$this->client->set($key, $value);
	}

	/**
	 * @param string $key
	 * @return mixed
	 */
	public function get($key)
	{
		if (!$value = $this->client->get($key)) {
			throw new KeyNotFoundException(sprintf('Key not found "%s"', $key));
		}

		return $value;
	}

	/**
	 * @param string $key
	 * @return bool
	 */
	public function has($key)
	{
		$this->client->get($key);

		return self::MEMCACHED_RES_NOTFOUND !== $this->client->getResultCode();
	}

	/**
	 * @param string[] $keys
	 * @return mixed[]
	 */
	public function multiGet(array $keys)
	{
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
