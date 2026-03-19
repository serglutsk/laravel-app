<?php

namespace Tests\Feature\Http\Controllers;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class HomeownerControllerTest extends TestCase
{
    public function test_index_returns_view(): void
    {
        $response = $this->get(route('names.index'));

        $response->assertStatus(200);
        $response->assertViewIs('parser.index');
    }

    public function test_upload_with_valid_csv_file(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith\nDr Jane Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('names.index'));
        $response->assertSessionHas('results');
        $response->assertSessionHas('success');
    }

    public function test_upload_without_file(): void
    {
        $response = $this->post(route('names.upload'), []);

        $response->assertSessionHasErrors('file');
        $this->assertTrue(session('errors')->has('file'));
    }

    public function test_upload_with_invalid_file_type(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->image('photo.jpg');

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertTrue(session('errors')->has('file'));
    }

    public function test_upload_with_oversized_file(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->create('large.csv', 3000); // 3MB, exceeds 2MB limit

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHasErrors('file');
        $this->assertTrue(session('errors')->has('file'));
    }

    public function test_upload_returns_parsed_results(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('results', function ($results) {
            return is_array($results) && count($results) > 0;
        });
    }

    public function test_upload_with_multiline_names(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr and Mrs Smith\nDr Jane Doe\nMr Tom Staff and Mr John Doe";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('names.index'));
        $response->assertSessionHas('results', function ($results) {
            return is_array($results) && count($results) >= 3;
        });
    }

    public function test_upload_success_message_is_translatable(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith";
        $file = UploadedFile::fake()->createWithContent('names.csv', $csvContent);

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertSessionHas('success', 'parser.success_message');
    }

    public function test_upload_with_txt_file(): void
    {
        Storage::fake('local');

        $csvContent = "Name\nMr John Smith";
        $file = UploadedFile::fake()->createWithContent('names.txt', $csvContent);

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('names.index'));
        $response->assertSessionHas('results');
    }

    public function test_upload_with_empty_file(): void
    {
        Storage::fake('local');

        $file = UploadedFile::fake()->createWithContent('names.csv', '');

        $response = $this->post(route('names.upload'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('names.index'));
        $response->assertSessionHas('results');
    }
}
