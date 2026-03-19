<?php

namespace App\Actions;

use App\DTO\PersonDTO;
use App\Services\NameParserService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class ProcessCsvFileAction
{
    public function __construct(
        private readonly NameParserService $nameParser
    ) {}

    /**
     * Process an uploaded CSV file and parse names.
     *
     * @return Collection<int, PersonDTO>
     *
     * @throws \RuntimeException
     */
    public function execute(UploadedFile $file): Collection
    {
        try {
            $path = $file->getRealPath();
            
            if ($path === false) {
                throw new \RuntimeException('Unable to get the file path.');
            }

            $data = array_map('str_getcsv', file($path));
            
            if (empty($data)) {
                Log::warning('CSV file is empty', ['filename' => $file->getClientOriginalName()]);
                return collect();
            }

            // Extract rows, skipping header
            $rows = array_column(array_slice($data, 1), 0);

            $results = collect();

            foreach ($rows as $row) {
                if (empty(trim($row))) {
                    continue;
                }

                $parsedNames = $this->nameParser->parse($row);
                $results = $results->concat($parsedNames);
            }

            Log::info('CSV file processed successfully', [
                'filename' => $file->getClientOriginalName(),
                'records_count' => $results->count(),
            ]);

            return $results;

        } catch (\Exception $e) {
            Log::error('Error processing CSV file', [
                'filename' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
