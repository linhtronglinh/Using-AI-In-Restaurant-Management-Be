<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckidBaiViet extends FormRequest
{ public function rules()
    {
        return [
            'id'         => 'exists:bai_viet1s,id',
        ];
    }

    public function messages()
    {
        return [
            'exists'      => ':attribute does not exist!',
        ];
    }

    public function attributes()
    {
        return [
            'id'          => 'BaiViet1',
        ];
    }
}
