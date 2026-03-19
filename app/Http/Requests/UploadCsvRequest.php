<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadCsvRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => __('parser.file_required'),
            'file.file' => __('parser.file_invalid'),
            'file.mimes' => __('parser.file_type'),
            'file.max' => __('parser.file_size'),
        ];
    }
}
