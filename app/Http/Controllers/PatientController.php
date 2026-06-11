<?php

namespace App\Http\Controllers;

use App\Http\Requests\PatientStoreRequest;
use App\Http\Requests\PatientUpdateRequest;
use App\Http\Resources\PatientCollection;
use App\Http\Resources\PatientResource;
use Illuminate\Support\Facades\Log;
use App\Models\Patient;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        $patients = Patient::all();
        return new PatientCollection($patients);
    }

    public function store(PatientStoreRequest $request)
    {
        Log::info('UPLOAD DEBUG', [
            'hasFile'     => $request->hasFile('image'),
            'fileKeys'    => array_keys($request->allFiles()),
            'contentType' => $request->header('content-type'),
        ]);

        $request->validated();

        // 1. Pastikan ada file
        if (!$request->hasFile('image')) {
            return response()->json([
                'error'  => 'No file detected',
                'fields' => $request->all(),
            ], 422);
        }

        $image = $request->file('image');

        // 2. Pastikan file valid
        if (!$image->isValid()) {
            return response()->json([
                'error' => 'Invalid image upload',
                'info'  => $image->getErrorMessage(),
            ], 422);
        }

        // 3. Pastikan direktori public/images ada dan writable
        $imagePath = public_path('images');
        if (!is_dir($imagePath)) {
            mkdir($imagePath, 0755, true);
        }
        if (!is_writable($imagePath)) {
            return response()->json([
                'error' => 'Directory public/images is not writable',
            ], 500);
        }

        // 4. Simpan file ke public/images
        $fileName    = date('YmdHi') . '_' . $image->getClientOriginalName();
        $fullPath    = public_path('images/' . $fileName);

        try {
            $image->move($imagePath, $fileName);
        } catch (Exception $e) {
            Log::error('Failed to move file', ['error' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Failed to move file',
                'message' => $e->getMessage(),
            ], 422);
        }

        // Helper: hapus file dari disk
        $deleteFile = function () use ($fullPath) {
            if (file_exists($fullPath)) {
                unlink($fullPath);
            }
        };

        // 5. Kirim ke Flask API
        try {
            $response = Http::timeout(900)
                ->attach(
                    'image',
                    file_get_contents($fullPath),
                    $fileName
                )
                ->post('http://localhost:5000/upload');
        } catch (Exception $e) {
            $deleteFile();
            Log::error('Failed to send image to Flask', ['error' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Failed to send image to Flask',
                'message' => $e->getMessage(),
            ], 500);
        }

        $responseData = $response->json();

        Log::info('Flask response', ['data' => $responseData]);

        // --- FIX: Periksa status Flask, jika bukan OK → stop, hapus file, kembalikan error ---
        $flaskStatus = Arr::get($responseData, 'status', '');

        if ($flaskStatus === 'NOT OK') {
            $deleteFile();
            return response()->json([
                'data' => ['message' => 'Tidak Terklasifikasi Gambar X-ray'],
            ], 400);
        }

        if ($flaskStatus !== 'OK') {
            // Error tak terduga dari Flask (status ERROR atau kosong)
            $deleteFile();
            return response()->json([
                'error'   => 'Unexpected response from Flask',
                'message' => Arr::get($responseData, 'message', 'Unknown error'),
            ], 500);
        }

        // 6. Simpan data ke database
        try {
            $data    = $request->validated();
            $patient = Patient::create([
                'name'         => Arr::get($data, 'name', $request->input('name')),
                'result'       => Arr::get($responseData, 'decision.label', null),
                'image'        => $fileName,
                'cnn_accuracy' => Arr::get($responseData, 'prediction.CNN.accuracy', null),
                'cnn_auc'      => Arr::get($responseData, 'prediction.CNN.auc', null),
                'cnn_label'    => Arr::get($responseData, 'prediction.CNN.label', null),
                'vit_accuracy' => Arr::get($responseData, 'prediction.ViT.accuracy', null),
                'vit_auc'      => Arr::get($responseData, 'prediction.ViT.auc', null),
                'vit_label'    => Arr::get($responseData, 'prediction.ViT.label', null),
            ]);
        } catch (Exception $e) {
            $deleteFile();
            Log::error('Failed to save patient data', ['error' => $e->getMessage()]);
            return response()->json([
                'error'   => 'Failed to save patient data',
                'message' => $e->getMessage(),
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'data'   => new PatientResource($patient),
        ]);
    }

    public function show(Request $request, Patient $patient)
    {
        return response()->json([
            'status' => 'success',
            'data'   => new PatientResource($patient),
        ]);
    }

    public function update(PatientUpdateRequest $request, Patient $patient)
    {
        $request->validated();

        $patient->update([
            'name'              => $request->name ?? $patient->name,
            'result'            => $request->result ?? $patient->result,
            'validation_doctor' => $request->validation_doctor ?? $patient->validation_doctor,
        ]);

        return new PatientResource($patient);
    }

    public function destroy(Request $request, Patient $patient)
    {
        if ($patient->image && file_exists(public_path('images/' . $patient->image))) {
            unlink(public_path('images/' . $patient->image));
        }

        $patient->delete();

        return response()->noContent();
    }
}