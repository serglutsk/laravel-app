<?php

namespace App\Actions;

use App\DTO\PersonDTO;
use App\Services\NameParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\LazyCollection;

class ProcessCsvFileAction
{
    public function __construct(
        private readonly NameParserService $nameParser
    ) {}

    /**
     * Process an uploaded CSV file using Lazy Collections.
     *
     * @return LazyCollection<int, PersonDTO>
     */
    public function execute(UploadedFile $file): LazyCollection
    {
        $path = $file->getRealPath();
        return LazyCollection::make(function () use ($path) {
            $handle = fopen($path, 'r');

            if ($handle === false) {
                throw new \RuntimeException("Cannot open file: {$path}");
            }

            // skipping header
            fgetcsv($handle);

            try {
                while (($row = fgetcsv($handle)) !== false) {
                    if (!isset($row[0]) || trim($row[0]) === '') {
                        continue;
                    }

                    yield $row[0];
                }
            } finally {
                fclose($handle);
            }
        })
            ->flatMap(function (string $rowValue) {
                return $this->nameParser->parse($rowValue);
            });
    }
}
