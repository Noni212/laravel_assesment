<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Mail\SendEmailToUser;
use App\Mail\SendVerificationCodeToUser;
use Image;
use Auth;
use Validator;

class UserController extends Controller
{

    public function sendResponse($result, $message)
    {
        $response = [
            'success' => true,
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, 200);
    }

    public function sendError($error, $errorMessages = [], $code = 404)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

    public function create(Request $request){
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'user_name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $image     =  $request->file('avatar');
        $filename  = time() . '.' . $image->getClientOriginalExtension();
        $path      = 'images/' . $filename;
        Image::make($image->getRealPath())->resize(256, 256)->save($path);

        $pin_code = rand(100000, 900000);
        $detail = [
            'name' => $data['user_name'],
            'user_name' => $data['user_name'],
            'email' => $data['email'],
            'avatar' => $filename,
            'user_role' => 'user',
            'register_at' => date("Y-m-d H:i:s"),
            'password' => bcrypt($data['password']),
            'pin_code'  => $pin_code
        ];
        \Mail::to($data['email'])->send(
            new SendVerificationCodeToUser($pin_code)
        );
        $user = User::create($detail);
        $success['token'] =  $user->createToken('MyApp')->accessToken;
        $success['name'] =  $user->name;

        return $this->sendResponse($success, 'User register successfully.');

    }


    public function login(Request $request){
        if(Auth::attempt(['email' => $request->email, 'password' => $request->password])){
            $user = Auth::user();
            $success['token'] =  $user->createToken('MyApp')-> accessToken;
            $success['name'] =  $user->name;
            return $this->sendResponse($success, 'User login successfully.');
        }
        else{
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }

    public function sendUserEmail(Request $request){
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'inviter_email' => 'required|email',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $adminUser = Auth::user()->user_role === "admin";
        if($adminUser){
            \Mail::to($request->inviter_email)->send(
                new SendEmailToUser()
            );
        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Only Admin Can Send Invitation']);
        }

    }

    public function updateProfile(Request $request){
        $data   = $request->all();
        $validator = Validator::make($request->all(), [
            'user_name' => 'min:4|max:20',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }
        if(Auth::user()->pin_code == null){
            $fields = ['user_name', 'email', 'user_role'];
            $detail = [];
            if($request->has('avatar')){
                $image     =  $request->file('avatar');
                $filename  = time() . '.' . $image->getClientOriginalExtension();
                $path      = 'images/' . $filename;
                Image::make($image->getRealPath())->resize(256, 256)->save($path);
                $detail['avatar'] =$filename;
            }
            foreach ($fields as $field){
                if(isset($data[$field])){
                    $detail[$field] = $data[$field];
                }
            }
            User::where('id', Auth::user()->id)->update($detail);
            return $this->sendResponse([], 'User updated successfully.');
        }else{
            return $this->sendError("Unauthorised", 'Please verify your email first then you can edit you profile');
        }
    }

    public function verifyLink(Request $request){
        $data = $request->all();
        $validator = Validator::make($request->all(), [
            'pin_code' => 'required',
        ]);
        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $verify = Auth::user()->pin_code == $data['pin_code'];
        if($verify){
            $user = User::find(Auth::user()->id);
            $user->pin_code = null;
            $user->save();
            return $this->sendResponse([], 'You have verified');
        }else{
            return $this->sendError('Unauthorised.', ['error'=>'Pincode Is Invalid']);
        }
    }
}
