<?php

namespace App\Providers\Facades;

use Illuminate\Support\Facades\Response;

class ApiResponseFacade extends Response{

    public function __construct()
    {
        // do something cool...
    }

    public static function apiResponse($data, $status=200, $message = '', $headers = [], $options=0) {
        //CourseAPI responses will return a data property
        //Compress this down into the $data variable
        if(is_array($data) && !empty($data['data'])) {$data = $data['data'];}
        if(is_object($data) && !empty($data->data)) {$data = $data->data;}

        return self::json($data, $status, $headers, $options);

        /*
        // Old format which doesn't follow the json spec / api-query format
        return self::json(
            [
                'success' => true,
                'message'=>$message,
                'data'=> $data,
            ],
            $status, $headers, $options);*/
    }

    /**
     * @param mixed $data
     * @param int $status=200
     * @param string $message = ''
     * @param array $headers = []
     * @param int $options=0
     */
    public function __call($name, $args) {
        switch($name) {
            case 'get':
                return self::apiResponse($args[0], 200, $args[1] ?? '', $args[2] ?? [], $args[3] ?? 0);
                break;
            case 'created':
                return self::apiResponse($args[0], 201, $args[1] ?? 'created', $args[2] ?? [], $args[3] ?? 0);
                break;
            case 'updated':
                return self::apiResponse($args[0], 200, $args[1] ?? 'updated', $args[2] ?? [], $args[3] ?? 0);
                break;
            case 'deleted':
                return self::apiResponse($args[0]??[], 200, $args[1] ?? 'deleted', $args[2] ?? [], $args[3] ?? 0);
                break;
        }
    }

    /**
     * @param int $status=404
     * @param string $message = ''
     * @param array $headers = []
     * @param int $options=0
     */
    public static function error($data=[], $status, $message = '', $headers = [], $options=0) {
        return self::json([
            'success'=> false,
            'message'=>$message,
            'error_code' => $status,
            'data'=> (object) $data,
        ], $status, $headers, $options);
    }
}