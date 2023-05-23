<?php
namespace App\Traits;

trait HasResponses{

    private function success($message, $data = []){
        return [
            'status' => 'success',
            'message' => $message,
            'data' => count($data) > 0 ? $data : null
        ];
    }

    private function error($message, $data = []){
        return [
            'status' => 'error',
            'message' => $message,
            'data' => count($data) > 0 ? $data : null
        ];
    }

}