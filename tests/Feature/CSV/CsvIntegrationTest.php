<?php

namespace Tests\Feature\CSV;

use App\Actions\ProcessCsvFileAction;
use App\Services\NameParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CsvIntegrationTest extends TestCase
{
    private ProcessCsvFileAction $action;

    protected function setUp(): void
    {
        parent::setUp();
        $this->action = new ProcessCsvFileAction(
            new NameParserService()
        );
    }

    public function test_process_real_world_csv_data(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
homeowner
Mr John Smith
Mrs Jane Smith
Mister John Doe
Mr Bob Lawblaw
Mr and Mrs Smith
Mr Craig Charles
Mr M Mackie
Mrs Jane McMaster
Mr Tom Staff and Mr John Doe
Dr P Gunn
Dr & Mrs Joe Bloggs
Ms Claire Robbo
Prof Alex Brogan
Mrs Faye Hughes-Eastwood
Mr F. Fredrickson
CSV;

        $file = UploadedFile::fake()->createWithContent('homeowners.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn($dto) => $dto->jsonSerialize())
            ->toArray();

        // Should parse multiple individuals
        $this->assertGreaterThan(16, count($result));

        // Verify some parsed entries
        $this->assertTrue(collect($result)->contains(function ($dto) {
            return $dto['title'] === 'Mr' && $dto['first_name'] === 'John' && $dto['last_name'] === 'Smith';
        }));

        $this->assertTrue(collect($result)->contains(function ($dto) {
            return $dto['title'] === 'Mrs' && $dto['first_name'] === 'Jane' && $dto['last_name'] === 'Smith';
        }));

        $this->assertTrue(collect($result)->contains(function ($dto) {
            return $dto['title'] === 'Mister' && $dto['first_name'] === 'John' && $dto['last_name'] === 'Doe';
        }));

        $this->assertTrue(collect($result)->contains(function ($dto) {
            return $dto['title'] === 'Ms' && $dto['first_name'] === 'Claire' && $dto['last_name'] === 'Robbo';
        }));

        $this->assertTrue(collect($result)->contains(function ($dto) {
            return $dto['title'] === 'Dr' && $dto['initial'] === 'P' && $dto['last_name'] === 'Gunn';
        }));
    }

    public function test_process_csv_handles_multi_person_entries(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
Name
Mr and Mrs Smith
Dr & Mrs Joe Bloggs
Mr Tom Staff and Mr John Doe
CSV;

        $file = UploadedFile::fake()->createWithContent('multi.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn($dto) => $dto->jsonSerialize())
            ->toArray();

        // Should parse at least 5 individuals (2+2+2)
        $this->assertGreaterThanOrEqual(5, count($result));
    }

    public function test_process_csv_with_all_title_variations(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
Name
Mr John Smith
Mrs Jane Smith
Miss Helen Taylor
Ms Claire Anderson
Dr Peter Thompson
Prof James Wilson
Mister Bob Martin
CSV;

        $file = UploadedFile::fake()->createWithContent('titles.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(7, $result);

        // Verify all titles are normalized
        $this->assertTrue(collect($result)->every(function ($dto) {
            return $dto['title'] !== null;
        }));

        // Verify titles are properly capitalized
        $titles = collect($result)->map(fn ($dto) => $dto['title'])->unique()->toArray();
        $this->assertContains('Mr', $titles);
        $this->assertContains('Mrs', $titles);
        $this->assertContains('Miss', $titles);
        $this->assertContains('Ms', $titles);
        $this->assertContains('Dr', $titles);
        $this->assertContains('Prof', $titles);
        $this->assertContains('Mister', $titles);
    }

    public function test_process_csv_handles_initial_variations(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
Name
Mr P Gunn
Mr F. Fredrickson
Mrs M McMaster
Dr J Smith
CSV;

        $file = UploadedFile::fake()->createWithContent('initials.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(4, $result);

        // All should have initials
        $this->assertTrue(collect($result)->every(function ($dto) {
            return $dto['initial'] !== null;
        }));

        // Initials should be single letter without dots
        $this->assertTrue(collect($result)->every(function ($dto) {
            return strlen($dto['initial']) === 1;
        }));
    }

    public function test_process_csv_with_complex_names(): void
    {
        Storage::fake('local');

        $csvContent = <<<CSV
Name
Mrs Faye Hughes-Eastwood
Mr John Smith-Jones
Ms Claire Robbo-King
CSV;

        $file = UploadedFile::fake()->createWithContent('complex.csv', $csvContent);

        $result = $this->action->execute($file)
            ->map(fn($dto) => $dto->jsonSerialize())
            ->toArray();

        $this->assertCount(3, $result);

        // Verify hyphenated names are preserved
        $complex = collect($result)->filter(function ($dto) {
            return str_contains($dto['last_name'] ?? '', '-');
        });

        $this->assertGreaterThanOrEqual(1, $complex->count());
    }

    public function test_process_csv_file_upload(): void
    {
        $response = $this->post(route('names.upload'), [
            'file' => UploadedFile::fake()->createWithContent('homeowners.csv', <<<CSV
homeowner
Mr John Smith
Mrs Jane Smith
Mister John Doe
CSV
            ),
        ]);

        $response->assertRedirect(route('names.index'));
        $response->assertSessionHas('results', function ($results) {
            return is_array($results) && count($results) >= 3;
        });
    }
}
