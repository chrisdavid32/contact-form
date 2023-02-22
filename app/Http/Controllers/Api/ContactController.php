<?php

namespace App\Http\Controllers\Api;

use App\Models\Contact;
use App\Mail\ContactForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactRequest;

class ContactController extends Controller
{
    public function storeContact(ContactRequest $request)
    {
        
        try {
            
                $data = $request->only(['name', 'email', 'message']);
               
                $file_attached = $request->file('attachment');
                $file_name = hexdec(uniqid()) . '.' . str_replace(' ', '_', $file_attached->getClientOriginalName());
                $destinationPath = public_path('/upload');
                $file_attached->move($destinationPath, $file_name);
                $data['attachment'] = $file_name;
                Contact::insert($data);
              
                Mail::to($data['email'])->send(new ContactForm());
                return response()->json(['status' => 200, 'message' => "Record created successful"]);
                 
            } catch (\Exception $e) {
                Log::error("Error submitting contact", $e->getTrace());
                    return ['success' => false, 'status' => 500, 'message' => 'Internal Server Error'];
            }

    }


    private function storeAttachment(UploadedFile $file, $path)
    {
        // Generate a unique filename for the file
        $filename = uniqid() . '.' . $file->getClientOriginalExtension();
        $storedPath = $file->storeAs($path, $filename, 'public');

        // Get the URL of the stored file
        $url = Storage::disk('public')->url($storedPath);

        // Return the stored path and URL as an associative array
        return [
            'stored_path' => $storedPath,
            'url' => $url,
        ];
    }
}
