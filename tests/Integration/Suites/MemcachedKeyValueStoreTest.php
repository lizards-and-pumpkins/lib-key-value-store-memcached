<?php

declare(strict_types = 1);

namespace LizardsAndPumpkins;

use LizardsAndPumpkins\DataPool\KeyValueStore\Exception\KeyNotFoundException;
use LizardsAndPumpkins\DataPool\KeyValueStore\Memcached\MemcachedKeyValueStore;

class MemcachedKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    const MEMCACHED_HOST = 'localhost';

    const MEMCACHED_PORT = 11211;

    /**
     * @var MemcachedKeyValueStore
     */
    private $keyValueStore;

    protected function setUp()
    {
        $client = new \Memcached();
        $client->addServer(self::MEMCACHED_HOST, self::MEMCACHED_PORT);
        $client->deleteMulti(['foo', 'key1', 'key2']);
        $this->keyValueStore = new MemcachedKeyValueStore($client);

        $this->skipIfMemcacheIsNotRunning($client);
    }

    public function testValueIsSetAndRetrieved()
    {
        $this->keyValueStore->set('foo', 'bar');
        $result = $this->keyValueStore->get('foo');

        $this->assertEquals('bar', $result);
    }

    public function testMultipleValuesAreSetAndRetrieved()
    {
        $keys = ['key1', 'key2'];
        $values = ['foo', 'bar'];
        $items = array_combine($keys, $values);

        $this->keyValueStore->multiSet($items);
        $result = $this->keyValueStore->multiGet(...$keys);

        $this->assertSame($items, $result);
    }

    public function testFalseIsReturnedIfKeyDoesNotExist()
    {
        $this->assertFalse($this->keyValueStore->has('foo'));
    }

    public function testExceptionIsThrownIfValueIsNotSet()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->assertFalse($this->keyValueStore->get('foo'));
    }

    /**
     * @param \Memcached $client
     */
    protected function skipIfMemcacheIsNotRunning($client)
    {
        $memcacheAddress = self::MEMCACHED_HOST . ':' . self::MEMCACHED_PORT;

        if (!isset($client->getStats()[$memcacheAddress])) {
            self::markTestSkipped('You need a running memcached to run the integration tests!');
        }
    }
}
