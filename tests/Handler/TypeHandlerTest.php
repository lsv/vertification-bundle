<?php

namespace Lsv\VertificationTest\Handler;

use Lsv\VertificationTest\CreateTestHandlerTrait;
use Lsv\VertificationTest\CreateTestTypeTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TypeHandlerTest extends TestCase
{

    use CreateTestTypeTrait;
    use CreateTestHandlerTrait;

    public function testWillThrowExceptionIfTypeKeyNotFound(): void
    {
        $lookup = 'Testing';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type with key "Testing" does not exists');
        $this->getHandler()->getType($lookup);
    }

    public function testWillThrowExceptionIfTypeClassnameNotFound(): void
    {
        $lookup = 'TestingClass';
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Type with className "TestingClass" does not exists');
        $this->getHandler()->getTypeFromClassName($lookup);
    }

    public function testShouldFindTwoTypes(): void
    {
        $key1 = $this->createType('key1');
        $key2 = $this->createType('key2');
        $this->assertCount(2, $this->getHandler([$key1, $key2])->getTypes());
    }

    public function testShouldFindOneTypeBecauseOneIsNotEnabled(): void
    {
        $key1 = $this->createType('key1');
        $key2 = $this->createType('key2', false);
        $this->assertCount(1, $this->getHandler([$key1, $key2])->getTypes());
    }

    public function testShouldFindOneTypeBecauseOneIsNotEnabledByUserfunc(): void
    {
        $callable = function (array $options) {
            return $options['enabled'];
        };

        $key1 = $this->createType('key1');
        $key2 = $this->createType('key2', $callable);
        $this->assertCount(1, $this->getHandler([$key1, $key2])->getTypes(['enabled' => false]));
    }

    public function testCanFindTypeWithOptions(): void
    {
        $callable = function (array $options) {
            return $options['enabled'];
        };

        $key1 = $this->createType('key1');
        $key2 = $this->createType('key2', $callable);

        $this->assertEquals(
            'key2',
            $this->getHandler([$key1, $key2])->getType('key2', ['enabled' => true])->getTitle(new Request())
        );
    }

    public function testCanFindTypeWithClassName(): void
    {
        $key1 = $this->createType('key1');
        $this->assertEquals(
            'key1',
            $this->getHandler([$key1])->getTypeFromClassName(\get_class($key1))->getTitle(new Request())
        );
    }
}
