<?php

namespace App\Services;

use App\DTO\PersonDTO;
use Illuminate\Support\Facades\Config;

class NameParserService
{
    private array $titles;

    private array $conjunctions;

    public function __construct()
    {
        $this->titles = Config::get('parser.titles', []);
        $this->conjunctions = Config::get('parser.conjunctions', []);
    }

    /**
     * Method to parse a full name string into an array of PersonDTOs.
     *
     * @return PersonDTO[]
     */
    public function parse(string $fullName): array
    {
        $fullName = trim($fullName);
        if (empty($fullName)) {
            return [];
        }

        // Split the string if it contains "and" or "&" but is not part of the header
        // Example: "Mr Tom Staff and Mr John Doe"
        if ($this->isMultiPersonString($fullName)) {
            return $this->parseMultiPerson($fullName);
        }

        return $this->parseSinglePattern($fullName);
    }

    private function isMultiPersonString(string $str): bool
    {
        foreach ($this->conjunctions as $conjunction) {
            if (str_contains(strtolower($str), " $conjunction ")) {
                return true;
            }
        }

        return false;
    }

    private function parseMultiPerson(string $str): array
    {
        // Case MultiPerson: "Mr and Mrs Smith" or "Dr & Mrs Joe Bloggs"
        $pattern = '/^(?<title1>[\w.]+)\s+(?:and|&)\s+(?<title2>[\w.]+)\s+(?<remainder>.+)$/i';

        if (preg_match($pattern, $str, $matches)) {
            $title1 = $matches['title1'];
            $title2 = $matches['title2'];
            $remainder = $matches['remainder'];

            if ($this->isTitle($title1) && $this->isTitle($title2)) {
                $secondPersonResults = $this->parseSinglePattern("$title2 $remainder");
                $lastName = $secondPersonResults[0]->last_name;

                return [
                    new PersonDTO($this->normalizeTitle($title1), null, null, $lastName),
                    ...$secondPersonResults,
                ];
            }
        }

        // Case: "Mr Tom Staff and Mr John Doe"
        $parts = preg_split('/\s+(?:and|&)\s+/i', $str);
        $allPartsResults = [];

        foreach ($parts as $part) {
            $allPartsResults[] = $this->parseSinglePattern($part);
        }

        return array_merge(...$allPartsResults);
    }

    private function parseSinglePattern(string $str): array
    {
        $parts = preg_split('/\s+/', trim($str));
        $title = null;
        $firstName = null;
        $initial = null;
        $lastName = null;

        // 1. Find Title
        if (count($parts) > 0 && $this->isTitle($parts[0])) {
            $title = $this->normalizeTitle(array_shift($parts));
        }

        // 2. Last Name - always the last element
        if (count($parts) > 0) {
            $lastName = array_pop($parts);
        }

        // 3. The rest is either a first name or an initial
        if (count($parts) > 0) {
            $middle = implode(' ', $parts);
            if ($this->isInitial($middle)) {
                $initial = str_replace('.', '', $middle);
            } else {
                $firstName = $middle;
            }
        }

        return [new PersonDTO($title, $firstName, $initial, $lastName)];
    }

    private function isTitle(string $word): bool
    {
        $cleanWord = rtrim($word, '.');

        return in_array(ucfirst(strtolower($cleanWord)), $this->titles, true);
    }

    private function normalizeTitle(string $word): string
    {
        $cleanWord = rtrim($word, '.');

        return ucfirst(strtolower($cleanWord));
    }

    private function isInitial(string $word): bool
    {
        return strlen(rtrim($word, '.')) === 1;
    }
}
