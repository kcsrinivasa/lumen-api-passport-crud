<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ImageTrait {

    /**
     * @param Request $request
     * @param fieldname
     * @param directory
     * @return $this|false|string
     */
    public static function uploadFile(Request $request, $fieldname = 'image', $directory = 'images' ) {

        if( $request->hasFile( $fieldname ) ) {

            $file = $request[$fieldname];
            $fileOriginalName = $file->getClientOriginalName(); 
            $extension = $file->extension(); 
            $size = $file->getSize(); 
            $fileName = Str::slug(substr($fileOriginalName,0,200)).time().'.'.$extension; 
            $filePath = 'uploads/'.$directory.'/'; 
            
            $file->move(app()->basePath('public/'.$filePath), $fileName); 
            $filePath = $filePath.$fileName;

            return $filePath;

        }
        return null;
    }

}