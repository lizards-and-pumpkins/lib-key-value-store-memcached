<?php

namespace LizardsAndPumpkins\DataPool\KeyValue\Memcached;

use LizardsAndPumpkins\KeyValue\KeyNotFoundException;

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
    private $stubClient;

    public function setUp()
    {
        $this->stubClient = $this->getMockBuilder(\Memcached::class)
            ->setMethods(['get', 'set', 'setMulti', 'getMulti', 'getResultCode'])
            ->getMock();
        $this->store = new MemcachedKeyValueStore($this->stubClient);
    }

    public function testValueIsSetAndRetrieved()
    {
        $key = 'key';
        $value = 'value';

        $this->stubClient->expects($this->once())->method('set');
        $this->stubClient->method('get')->willReturn($value);

        $this->store->set($key, $value);
        $this->assertEquals($value, $this->store->get($key));
    }

    public function testFalseIsReturnedIfKeyDoesNotExist()
    {
        $this->stubClient->expects($this->once())->method('getResultCode')
            ->willReturn(MemcachedKeyValueStore::MEMCACHED_RES_NOTFOUND);

        $this->assertFalse($this->store->has('foo'));
    }

    public function testExceptionIsThrownIfValueIsNotSet()
    {
        $this->setExpectedException(KeyNotFoundException::class);
        $this->store->get('not set key');
    }

    public function testMultipleKeysAreSetAndRetrieved()
    {
        $keys = ['key1', 'key2'];
        $values = ['foo', 'bar'];
        $items = array_combine($keys, $values);

        $this->stubClient->expects($this->once())->method('setMulti');

        $this->store->multiSet($items);

        $this->stubClient->expects($this->once())->method('getMulti')->willReturn($values);

        $result = $this->store->multiGet($keys);

        $this->assertSame($values, $result);
    }
}
