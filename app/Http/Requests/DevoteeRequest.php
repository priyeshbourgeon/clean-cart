<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class DevoteeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|max:200',
            'email' => 'nullable|email|unique:users.user_dtl,email',
            'mobile' => 'required|numeric|unique:users.user_dtl,mobile',
            'house' => 'nullable',
            'street' => 'nullable',
            'post' => 'nullable',
            'district' => 'nullable',
            'state' => 'nullable',
            'pincode' => 'nullable|numeric',
        ];
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Validation errors',
            'data'      => $validator->errors()
        ]));
    }

    public function messages() 
    {
        return [
            'name.required' => 'Please enter devotee name',
            'email.email' => 'Given email is invalid!',
            'email.unique' => 'Email is already taken!',
            'mobile.required' => 'Please enter devotee mobile no.',
            'mobile.unique' => 'Mobile no. is already taken!',
        ];
    }
}
