<?php

namespace App\Http\Controllers;

use App\Actions\ProcessCsvFileAction;
use App\Http\Requests\UploadCsvRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class HomeownerController extends Controller
{
    public function __construct(
        private readonly ProcessCsvFileAction $csvProcessor
    ) {}


        public function index(): View
        {
            $data = [];
            if (session()->has('results')) {
                $data['results'] = session('results');
            }
            if (session()->has('success')) {
                $data['success'] = session('success');
            }

            return view('parser.index', $data);
        }



    /**
     * Process the uploaded CSV file.
     */
    public function upload(UploadCsvRequest $request): RedirectResponse
    {
        $results = $this->csvProcessor->execute($request->file('file'));
        return redirect()
            ->route('names.index')
            ->with([
                'success'=>'parser.success_message',
                'results' => $results,
            ]);
    }
}
