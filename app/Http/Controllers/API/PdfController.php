<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Kid;
use App\User;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Client\ResponseSequence;
use Illuminate\Support\Facades\Storage;

class PdfController extends Controller
{
    public function viewPDF() {
        $pdf = Pdf::loadView('pdf.error');  
        return $pdf->setPaper('a4', 'portrait')->stream('error.pdf');
    }
    public function ExportParents(Request $request)
    {
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            $pdf = Pdf::loadView('pdf.error');  
            return $pdf->setPaper('a4', 'portrait')->stream('error.pdf');
        }
        $data = json_decode($request['data']);
        $parents = array();
        $parents_ = array();
        $filter = '';
        if($data->search === '' && $data->filter == 'all') {
            $parents_ = User::where('access_level', '2')->get();
            $filter = 'all parents';
        }elseif($data->filter != 'all') {
            $parents_ = User::where('access_level', '2')
            ->where('relationship', $data->filter)->get();
            $filter = $data->filter.'s';
        }elseif($data->search) {
            $parents_ = User::where('access_level', '2')
            ->where('name', 'like', '%'.$data->search.'%')->get();
            $filter = '';
        }
        if ($parents_) {
            foreach ($parents_ as $key => $value) {
                $kid = Kid::where('user_id', $value->id)->count();
                $parent = new User();
                $parent->name = $value->name;
                $parent->kids = $kid;
                $parent->relationship = $value->relationship;
                $parent->phone = $value->phone;
                $parent->emergency_number = $value->emergency_number;
                array_push($parents, $parent);
            }
        }
        $time = Carbon::now();
        Pdf::setOption(['dpi' => 150, 'defaultFont' => 'sans-serif']);
        $pdf = Pdf::loadView('pdf.parentExport', array('user' => $user, 'parents' => $parents, 'filter' => $filter, 'time' => $time));
        return $pdf->setPaper('a4', 'portrait')->stream('parents_export.pdf');
    }
    public function Gltf() {

        // Get the storage path of the GLTF file
        $filePath = storage_path('app/public/models/scene.gltf');

        // Check if the file exists
        if (!file_exists($filePath)) {
            return response()->json(['error' => 'GLTF file not found.'], 404);
        }

        // Set the appropriate Content-Type header
        $headers = [
            'Content-Type' => 'model/gltf+json',
        ];

        // Return the file response
        return response()->json($filePath, 200);

        $path = storage_path('app/public/models');
        if(!is_dir($path)) {
            return response()->json('folder not found', 404);
        }
        $files = glob("$path/*");

    // Prepare the response
    $response = [];

    foreach ($files as $file) {
        $filename = basename($file);
        $fileContent = file_get_contents($file);

        $response[] = [
            'filename' => $filename,
            'content' => base64_encode($fileContent),
        ];
    }
    return response()->json($response);

    }

}
