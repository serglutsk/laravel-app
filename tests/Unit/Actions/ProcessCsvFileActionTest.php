<?php

namespace Tests\Unit\Actions;

use App\Actions\ProcessCsvFileAction;
use App\Services\NameParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProcessCsvFileActionTest extends TestCase
{
    private ProcessCsvFileAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ProcessCsvFileAction(
            new NameParserService()
        );
    }

    public function test_process_valid_csv_file(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file);

        $this->assertTrue($result->isNotEmpty());
        $this->assertCount(2, $result);
    }

    public function test_process_csv_with_multi_person_entries(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr and Mrs Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file);

        $this->assertGreaterThanOrEqual(2, $result->count());
    }

    public function test_process_csv_skips_empty_rows(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\n\n\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $result = $this->action->execute($file);

        $this->assertCount(2, $result);
    }

    public function test_process_empty_csv_file(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('names.csv', '');

        $result = $this->action->execute($file);

        $this->assertTrue($result->isEmpty());
    }

    public function test_process_logs_success(): void
    {
        Storage::fake('local');
        Log::shouldReceive('info')->once()->with('CSV file processed successfully', \Mockery::any());

        $csvContent = "Name\nMr John Smith";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $this->action->execute($file);
    }

    public function test_process_logs_empty_file_warning(): void
    {
        Storage::fake('local');
        Log::shouldReceive('warning')->once()->with('CSV file is empty', \Mockery::any());

        $file = UploadedFile::fake()->createWithContent('names.csv', '');

        $this->action->execute($file);
    }

    public function test_process_returns_collection(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\nDr Jane Doe";
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

        $result = $this->action->execute($file);

        $dto = $result->first();
        $this->assertEquals('Dr', $dto->title);
        $this->assertNull($dto->first_name);
        $this->assertEquals('J', $dto->initial);
        $this->assertEquals('Smith', $dto->last_name);
    }
}
