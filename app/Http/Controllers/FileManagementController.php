<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FileManagementController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function upload(Request $_request)
    {
        try {
            if ($_request->hasFile('file')) {
                $file = $_request->file('file');
                if (is_array($file)) {
                    $file = $file[0];
                }

                $fileName = time() .  '.' . $file->getClientOriginalExtension();
                $destinationPath = storage_path(STORE_TEMP);
                $file->move($destinationPath, $fileName);
                $fileFullPath = $destinationPath . '/' . $fileName;
            } else {
                $fileFullPath = '';
            }

            return response()->json([
                'error' => false,
                "message" => "File uploaded successfully",
                'file' => $fileFullPath
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public static function deleteFile($_file)
    {
        if ($_file === "") {
            return response()->json([
                'error' => true,
                'message' => 'File not found'
            ], 500);
        }

        try {
            if (file_exists($_file)) {
                unlink($_file);
            }
            return response()->json([
                'error' => false,
                "message" => "File deleted successfully",
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public static function copyFileTemp($_urls)
    {
        try {
            $destinationPath = storage_path(STORE_IMAGE);
            if (!file_exists($destinationPath)) {
                mkdir($destinationPath, 0777, true);
            }

            if (is_array($_urls)) {
                foreach ($_urls as $url) {
                    if (file_exists($url)) {
                        copy($url, str_replace(STORE_TEMP, STORE_IMAGE, $url));
                    }
                }
            } else {
                if (file_exists($_urls)) {
                    copy($_urls, str_replace(STORE_TEMP, STORE_IMAGE, $_urls));
                }
            }

            return response()->json([
                'error' => false,
                'message' => 'Copy file success',
                'data' => str_replace(STORE_TEMP, STORE_IMAGE, $_urls)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => $e->getMessage(),
                'data' => ''
            ], 500);
        }
    }

    public static function deleteFileTemp()
    {
        $TIME_FIVE_MINUTES = 5 * 60;
        try {
            $files = scandir(storage_path(STORE_TEMP));
            foreach ($files as $file) {
                if ($file != "." && $file != "..") {
                    $fileName = explode(".", $file)[0];
                    $timeFile = intval($fileName);
                    if ($timeFile <= time() - $TIME_FIVE_MINUTES) {
                        unlink(storage_path(STORE_TEMP . '/' . $file));
                    }
                }
            }

            return response()->json([
                'error' => false,
                'message' => 'Delete file temp success'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => true,
                'message' => "Error deleteFileTemp: {$e->getMessage()}"
            ], 500);
        }
    }
}
