<?php

namespace Brera\KeyValue\Memcached;

class MemcachedKeyValueStoreIntegrationTest extends \PHPUnit_Framework_TestCase
{
	const MEMCACHED_HOST = 'localhost';

	const MEMCACHED_PORT = 11211;

	/**
	 * @var MemcachedKeyValueStore
	 */
	private $keyValueStore;

	protected function setUp()
	{
		if (!class_exists(\Memcached::class)) {
			$this->markTestSkipped(
				sprintf('Memcached is not available on %s:%s', self::MEMCACHED_HOST, self::MEMCACHED_PORT)
			);
		}

		$client = new \Memcached();
		$client->addServer(self::MEMCACHED_HOST, self::MEMCACHED_PORT);
		$client->deleteMulti(['foo', 'key1', 'key2']);

		$this->keyValueStore = new MemcachedKeyValueStore($client);
	}

	/**
	 * @test
	 */
	public function itShouldSetAndGetAValue()
	{
		$this->keyValueStore->set('foo', 'bar');
		$result = $this->keyValueStore->get('foo');

		$this->assertEquals('bar', $result);
	}

	/**
	 * @test
	 */
	public function itShouldSetAndGetMultipleValues()
	{
		$keys = ['key1', 'key2'];
		$values = ['foo', 'bar'];
		$items = array_combine($keys, $values);

		$this->keyValueStore->multiSet($items);
		$result = $this->keyValueStore->multiGet($keys);

		$this->assertSame($items, $result);
	}

	/**
	 * @test
	 */
	public function itShouldReturnFalseItKeyDoesNotExist()
	{
		$this->assertFalse($this->keyValueStore->has('foo'));
	}
}
