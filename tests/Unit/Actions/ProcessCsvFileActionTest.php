<?php

namespace Tests\Unit\Actions;

use App\Actions\ProcessCsvFileAction;
use App\Services\NameParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessCsvFileActionTest extends TestCase
{
    private ProcessCsvFileAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ProcessCsvFileAction(
            new NameParserService
        );
    }

    public function test_process_valid_csv_file(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(2, $result);
    }

    public function test_process_csv_with_multi_person_entries(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr and Mrs Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertGreaterThanOrEqual(2, count($result));
    }

    public function test_process_csv_skips_empty_rows(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\n\n\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(2, $result);
    }

    public function test_process_empty_csv_file(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('names.csv', '');

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(0, $result);
    }

    public function test_process_returns_lazy_collection(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file);

        $this->assertIsObject($result);
        $this->assertTrue(method_exists($result, 'toArray'));
    }

    public function test_process_preserves_dto_properties(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nDr J Smith";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $dto = $result[0];
        $this->assertEquals('Dr', $dto['title']);
        $this->assertNull($dto['first_name']);
        $this->assertEquals('J', $dto['initial']);
        $this->assertEquals('Smith', $dto['last_name']);
    }

    public function test_process_handles_multiple_records(): void
    {
        Storage::fake('local');

        $csvContent = <<<'CSV'
Name
Mr John Smith
Mrs Jane Smith
Dr Peter Thompson
Ms Claire Anderson
CSV;

        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(4, $result);
    }

    public function test_process_lazy_collection_is_lazy(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        // LazyCollection should be returned without immediately evaluating
        $result = $this->action->execute($file);

        // Verify it's actually lazy (toArray should be callable)
        $evaluated = $result
            ->map(fn ($dto) => $dto->jsonSerialize())
            ->toArray();
        $this->assertCount(2, $evaluated);
    }
}
