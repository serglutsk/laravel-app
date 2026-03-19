<?php

namespace Tests\Unit\Services;

use App\DTO\PersonDTO;
use App\Services\NameParserService;
use PHPUnit\Framework\TestCase;

class NameParserServiceTest extends TestCase
{
    private NameParserService $parserService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserService = new NameParserService();
    }

    public function test_parse_single_person_with_title_and_last_name(): void
    {
        $result = $this->parserService->parse('Mr John Smith');

        $this->assertCount(1, $result);
        $this->assertInstanceOf(PersonDTO::class, $result[0]);
        $this->assertEquals('Mr', $result[0]->title);
        $this->assertEquals('John', $result[0]->first_name);
        $this->assertNull($result[0]->initial);
        $this->assertEquals('Smith', $result[0]->last_name);
    }

    public function test_parse_person_without_title(): void
    {
        $result = $this->parserService->parse('John Smith');

        $this->assertCount(1, $result);
        $this->assertNull($result[0]->title);
        $this->assertEquals('John', $result[0]->first_name);
        $this->assertEquals('Smith', $result[0]->last_name);
    }

    public function test_parse_person_with_initial(): void
    {
        $result = $this->parserService->parse('Dr J Smith');

        $this->assertCount(1, $result);
        $this->assertEquals('Dr', $result[0]->title);
        $this->assertNull($result[0]->first_name);
        $this->assertEquals('J', $result[0]->initial);
        $this->assertEquals('Smith', $result[0]->last_name);
    }

    public function test_parse_person_with_initial_and_dot(): void
    {
        $result = $this->parserService->parse('Dr J. Smith');

        $this->assertCount(1, $result);
        $this->assertEquals('Dr', $result[0]->title);
        $this->assertNull($result[0]->first_name);
        $this->assertEquals('J', $result[0]->initial);
        $this->assertEquals('Smith', $result[0]->last_name);
    }

    public function test_parse_multi_person_with_and(): void
    {
        $result = $this->parserService->parse('Mr and Mrs Smith');

        $this->assertCount(2, $result);
        $this->assertEquals('Mr', $result[0]->title);
        $this->assertNull($result[0]->first_name);
        $this->assertEquals('Smith', $result[0]->last_name);
        $this->assertEquals('Mrs', $result[1]->title);
        $this->assertNull($result[1]->first_name);
        $this->assertEquals('Smith', $result[1]->last_name);
    }

    public function test_parse_multi_person_with_ampersand(): void
    {
        $result = $this->parserService->parse('Dr & Mrs Joe Bloggs');

        $this->assertGreaterThanOrEqual(2, count($result));
        $this->assertEquals('Dr', $result[0]->title);
        $this->assertEquals('Bloggs', $result[0]->last_name);
    }

    public function test_parse_multi_person_with_different_names(): void
    {
        $result = $this->parserService->parse('Mr Tom Staff and Mr John Doe');

        $this->assertCount(2, $result);
        $this->assertEquals('Mr', $result[0]->title);
        $this->assertEquals('Tom', $result[0]->first_name);
        $this->assertEquals('Staff', $result[0]->last_name);
        $this->assertEquals('Mr', $result[1]->title);
        $this->assertEquals('John', $result[1]->first_name);
        $this->assertEquals('Doe', $result[1]->last_name);
    }

    public function test_parse_empty_string_returns_empty_array(): void
    {
        $result = $this->parserService->parse('');

        $this->assertCount(0, $result);
    }

    public function test_parse_whitespace_only_returns_empty_array(): void
    {
        $result = $this->parserService->parse('   ');

        $this->assertCount(0, $result);
    }

    public function test_parse_person_with_multiple_first_names(): void
    {
        $result = $this->parserService->parse('Mr John Peter Smith');

        $this->assertCount(1, $result);
        $this->assertEquals('Mr', $result[0]->title);
        $this->assertEquals('John Peter', $result[0]->first_name);
        $this->assertEquals('Smith', $result[0]->last_name);
    }

    public function test_parse_normalizes_title_case(): void
    {
        $result = $this->parserService->parse('mr john smith');

        $this->assertCount(1, $result);
        $this->assertEquals('Mr', $result[0]->title); // Should normalize to Mr
    }

    public function test_parse_prof_title(): void
    {
        $result = $this->parserService->parse('Prof James Wilson');

        $this->assertCount(1, $result);
        $this->assertEquals('Prof', $result[0]->title);
        $this->assertEquals('James', $result[0]->first_name);
        $this->assertEquals('Wilson', $result[0]->last_name);
    }

    public function test_parse_miss_title(): void
    {
        $result = $this->parserService->parse('Miss Jane Doe');

        $this->assertCount(1, $result);
        $this->assertEquals('Miss', $result[0]->title);
        $this->assertEquals('Jane', $result[0]->first_name);
        $this->assertEquals('Doe', $result[0]->last_name);
    }
}
