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
        $this->mockClient = $this->getMockBuilder(\Memcached::class)
            ->setMethods(['get', 'set', 'setMulti', 'getMulti', 'getResultCode'])
            ->getMock();
        $this->store = new MemcachedKeyValueStore($this->mockClient);
    }

    public function testValueIsSetAndRetrieved()
    {
        $key = 'key';
        $value = 'value';

        $this->mockClient->expects($this->once())->method('set')->with($key, $value);
        $this->mockClient->method('get')->willReturn($value);

        $this->store->set($key, $value);
        $this->assertEquals($value, $this->store->get($key));
    }

    public function testFalseIsReturnedIfKeyDoesNotExist()
    {
        $this->mockClient->method('getResultCode')->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);
        $this->assertFalse($this->store->has('foo'));
    }

    public function testExceptionIsThrownIfValueIsNotSet()
    {
        $this->expectException(KeyNotFoundException::class);
        $this->mockClient->method('getResultCode')->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);
        $this->store->get('foo');
    }

    public function testMultipleKeysAreSetAndRetrieved()
    {
        $keys = ['key1', 'key2'];
        $values = ['foo', 'bar'];
        $items = array_combine($keys, $values);

        $this->mockClient->expects($this->once())->method('setMulti')->with($items);

        $this->store->multiSet($items);

        $this->mockClient->expects($this->once())->method('getMulti')->willReturn($values);

        $result = $this->store->multiGet($keys);

        $this->assertSame($values, $result);
    }
}
