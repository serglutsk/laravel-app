<?php

namespace Tests\Feature\Http\Requests;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadCsvRequestTest extends TestCase
{
    public function test_file_is_required(): void
    {
        $response = $this->post(route('names.upload'), []);

        $response->assertSessionHasErrors('file');
    }

    public function test_file_must_be_a_file(): void
    {
        $response = $this->post(route('names.upload'), [
            'file' => 'not-a-file',
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_file_must_be_csv_or_txt(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_file_must_not_exceed_2mb(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('large.csv', 3000); // 3MB

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
    }

    public function test_valid_csv_file_passes_validation(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('names.csv', 'Name\nJohn Smith');

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionDoesntHaveErrors('file');
    }

    public function test_valid_txt_file_passes_validation(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('names.txt', 'Name\nJohn Smith');

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionDoesntHaveErrors('file');
    }

    public function test_validation_error_messages(): void
    {
        $response = $this->post(route('names.upload'), []);

        $response->assertSessionHasErrors('file');
        $this->assertStringContainsString('required', session('errors')->first('file'));
    }

    public function test_file_under_2mb_passes_validation(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('names.csv', 1024); // 1MB

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionDoesntHaveErrors('file');
    }

    public function test_file_exactly_2mb_passes_validation(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('names.csv', 2048); // 2MB

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionDoesntHaveErrors('file');
    }
}
