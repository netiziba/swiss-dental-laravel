<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('admin') || $user->hasRole('dentist')) {
            $patientId = $request->query('patient_id', $user->id);
            $documents = Document::where('user_id', $patientId)->get();
        } else {
            $documents = Document::where('user_id', $user->id)->get();
        }

        return response()->json($documents);
    }

    public function store(Request $request)
    {
        $request->validate([
            'file'     => 'required|file|max:20480',
            'name'     => 'required|string|max:255',
            'category' => 'nullable|in:insurance,xray,invoice,report,other',
        ]);

        $file = $request->file('file');
        $path = $file->store("documents/{$request->user()->id}", 'local');

        $document = Document::create([
            'user_id'   => $request->user()->id,
            'name'      => $request->name,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'category'  => $request->category,
        ]);

        return response()->json($document, 201);
    }

    public function show(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        return response()->json($document);
    }

    public function download(Request $request, Document $document)
    {
        $this->authorize('view', $document);

        return Storage::download($document->file_path, $document->name);
    }

    public function destroy(Request $request, Document $document)
    {
        $this->authorize('delete', $document);

        Storage::delete($document->file_path);
        $document->delete();

        return response()->json(null, 204);
    }
}
