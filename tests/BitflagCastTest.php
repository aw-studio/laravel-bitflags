<?php

namespace AwStudio\Bitflags\Tests;

use AwStudio\Bitflags\Casts\Bitflags;
use AwStudio\Bitflags\Tests\TestSupport\TestModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;

class BitflagCastTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_will_cast_the_attribute_as_an_array()
    {
        $model = TestModel::create(['status' => 0]);
        $this->assertIsArray($model->status);
    }

    public function test_it_will_cast_null_to_an_empty_array()
    {
        $this->assertSame([], (new Bitflags)->get(new TestModel, 'status', null, []));
    }

    public function test_it_will_transform_an_array_to_bitmask_on_update()
    {
        $model = TestModel::create(['status' => 0]);
        $model->update(['status' => [1, 2]]);
        $this->assertDatabaseHas('test_models', ['status' => 3]);
    }

    public function test_bitflag_may_be_set_by_array_or_int()
    {
        TestModel::create(['status' => 1]);
        $this->assertDatabaseHas('test_models', ['status' => 1]);

        TestModel::create(['status' => [1, 4]]);
        $this->assertDatabaseHas('test_models', ['status' => [5]]);
    }

    public function test_each_array_entry_must_be_power_of_two()
    {
        $this->expectException(InvalidArgumentException::class);
        TestModel::create(['status' => [3]]);
    }

    public function test_in_bitmask_method()
    {
        $model = TestModel::factory(['status' => [1, 4]])->create();
        $this->assertTrue(inBitmask(1, $model->status));
        $this->assertTrue(inBitmask(4, $model->status));
        $this->assertFalse(inBitmask(2, $model->status));
    }
}
