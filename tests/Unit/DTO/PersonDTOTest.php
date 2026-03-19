<?php

namespace Tests\Unit\DTO;

use App\DTO\PersonDTO;
use PHPUnit\Framework\TestCase;

class PersonDTOTest extends TestCase
{
    public function test_dto_can_be_instantiated(): void
    {
        $dto = new PersonDTO(
            title: 'Dr',
            first_name: 'John',
            initial: null,
            last_name: 'Smith'
        );

        $this->assertEquals('Dr', $dto->title);
        $this->assertEquals('John', $dto->first_name);
        $this->assertNull($dto->initial);
        $this->assertEquals('Smith', $dto->last_name);
    }

    public function test_dto_can_have_all_null_properties(): void
    {
        $dto = new PersonDTO(
            title: null,
            first_name: null,
            initial: null,
            last_name: null
        );

        $this->assertNull($dto->title);
        $this->assertNull($dto->first_name);
        $this->assertNull($dto->initial);
        $this->assertNull($dto->last_name);
    }

    public function test_dto_implements_json_serializable(): void
    {
        $dto = new PersonDTO(
            title: 'Dr',
            first_name: 'Jane',
            initial: null,
            last_name: 'Doe'
        );

        $this->assertInstanceOf(\JsonSerializable::class, $dto);
    }

    public function test_dto_json_serialize_returns_array(): void
    {
        $dto = new PersonDTO(
            title: 'Mr',
            first_name: 'John',
            initial: 'J',
            last_name: 'Smith'
        );

        $result = $dto->jsonSerialize();

        $this->assertIsArray($result);
        $this->assertEquals([
            'title' => 'Mr',
            'first_name' => 'John',
            'initial' => 'J',
            'last_name' => 'Smith',
        ], $result);
    }

    public function test_dto_can_be_json_encoded(): void
    {
        $dto = new PersonDTO(
            title: 'Dr',
            first_name: 'Jane',
            initial: null,
            last_name: 'Doe'
        );

        $json = json_encode($dto);

        $this->assertIsString($json);
        $this->assertStringContainsString('Dr', $json);
        $this->assertStringContainsString('Jane', $json);
        $this->assertStringContainsString('Doe', $json);
    }

    public function test_dto_json_preserves_null_values(): void
    {
        $dto = new PersonDTO(
            title: 'Miss',
            first_name: null,
            initial: 'M',
            last_name: 'Johnson'
        );

        $result = $dto->jsonSerialize();

        $this->assertNull($result['first_name']);
        $this->assertArrayHasKey('first_name', $result);
    }
}
