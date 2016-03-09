<?php

namespace LizardsAndPumpkins\DataPool\KeyValue\Memcached;

use LizardsAndPumpkins\DataPool\KeyValue\Exception\KeyNotFoundException;

/**
 * @covers  \LizardsAndPumpkins\DataPool\KeyValue\Memcached\MemcachedKeyValueStore
 */
class MemcachedKeyValueStoreTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MemcachedKeyValueStore
     */
    private $store;

    /**
     * @var \Memcached|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockClient;

    public function setUp()
    {
        $this->mockClient = $this->getMock(\Memcached::class, [], [], '', false);
        $this->store = new MemcachedKeyValueStore($this->mockClient);
    }

    public function testSettingValueIsDelegatedToClient()
    {
        $key = 'key';
        $value = 'value';

        $this->mockClient->expects($this->once())->method('set')->with($key, $value);
        $this->store->set($key, $value);
    }

    public function testExceptionIsThrownDuringAttemptToGetAValueWhichIsNotSet()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->mockClient->method('getResultCode')->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);
        $this->store->get('not set key');
    }

    public function testGettingValueIsDelegatedToClient()
    {
        $key = 'key';
        $value = 'value';

        $this->mockClient->method('get')->with($key)->willReturn($value);

        $this->assertEquals($value, $this->store->get($key));
    }

    public function testCheckingKeyExistenceIsDelegatedToClient()
    {
        $this->mockClient->method('getResultCode')->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);
        $this->assertFalse($this->store->has('foo'));
    }

    public function testSettingMultipleKeysIsDelegatedToClient()
    {
        $items = ['key1' => 'foo', 'key2' => 'bar'];

        $this->mockClient->expects($this->once())->method('setMulti')->with($items);
        $this->store->multiSet($items);
    }

    public function testEmptyArrayIsReturnedIfRequestedSnippetKeysArrayIsEmpty()
    {
        $this->assertSame([], $this->store->multiGet([]));
    }

    public function testGettingMultipleKeysIsDelegatedToClient()
    {
        $items = ['key1' => 'foo', 'key2' => 'bar'];
        $keys = array_keys($items);

        $this->mockClient->expects($this->once())->method('getMulti')->with($keys)->willReturn($items);

        $this->assertSame($items, $this->store->multiGet($keys));
    }
}
