<?php

namespace App\Modules\Storage\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class StorageController extends Controller
{
    protected $disk;

    public function __construct()
    {
        $this->disk = \Storage::disk('qiniu');
    }

    /**
     * @OA\Post(
     *      path="/api/storage/v1/upload/file", tags={"公共-文件存储"}, summary="上传文件",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="file", type="string", description="文件", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="",),
     * )
     */
    public function uploadFile(Request $request)
    {
        $mimes = $this->parseMimis(config('storage.allow_uoload_file_mimes'));

        $file = $request->file('file');
        if (empty($file)) {
            return $this->error('storage::messages.please_select_file');
        }

        $validator = Validator::make(
            [
                'file' => $file
            ],
            [
                //'file' => 'required|file|mimes:' . $mimes,
            ],
            [
                'file.required' => trans('storage::messages.please_select_file'),
                'file.file'     => trans('storage::messages.please_select_file'),
                'file.mimes'    => trans('storage::messages.can_only_upload_mimis_files', ['mimis' => $mimes]),
            ]
        );

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $path = $this->disk->put($this->getPathPrefix('images'), $file);
        if ($path) {
            return $this->success(
                'storage::messages.upload_success',
                [
                    'path' => $this->disk->url($path),
                ]
            );
        }
    }

    /**
     * @OA\Post(
     *      path="/api/storage/v1/upload/image", tags={"公共-文件存储"}, summary="上传图片",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              @OA\Property( property="file", type="string", description="图片", example="" ),
     *          )
     *      ),
     *      @OA\Response(response="200",description="",),
     * )
     */
    public function uploadImage(Request $request)
    {
        $mimes = $this->parseMimis(config('storage.allow_uoload_image_mimes'));

        $file = $request->file('file');
        if (empty($file)) {
            return $this->error('storage::messages.please_select_file');
        }

        $validator = Validator::make(
            [
                'file' => $file
            ],
            [
                'file' => 'required|file|mimes:' . $mimes,
            ],
            [
                'file.required' => trans('storage::messages.please_select_file'),
                'file.file'     => trans('storage::messages.please_select_file'),
                'file.mimes'    => trans('storage::can_only_upload_mimis_files', ['mimis' => $mimes]),
            ]
        );

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }

        $path = $this->disk->put($this->getPathPrefix('images'), $file);
        if ($path) {
            return $this->success(
                'storage::messages.upload_success',
                [
                    'path' => $this->disk->url($path),
                ]
            );
        }

        return $this->error('storage::messages.upload_error');
    }

    protected function getPathPrefix($path)
    {
        return 'public/' . $path . '/' . date('Ymd');
    }

    protected function parseMimis($mimis)
    {
        if (is_array($mimis)) {
            return implode(',', $mimis);
        }

        return $mimis;
    }
}
