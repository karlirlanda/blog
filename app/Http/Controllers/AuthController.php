<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Authentication\LoginRequest;
use App\Http\Requests\Authentication\RegisterRequest;
use App\Http\Requests\Authentication\ChangePasswordRequest;

class AuthController extends Controller
{
    public function register(RegisterRequest $request)
    {
        $request['password'] = Hash::make($request->password);

        $data = User::create($request->all());

        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $data = [
                'action'  => 'register',
                'after_data' => $data,
                'user_id' => $data->id,
                'user_type' => $data->type,
                'table_name'  => 'user',
                'table_id'  => $data->id,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform(),
            ];

            (new ActivityLogController)->create($data);
        }

        return response()->json([
            'status' => $status
        ]);
    }

    public function login(LoginRequest $request)
    {
        $data = User::where('username', $request['username'])->first();

        if (!$data || !Hash::check($request->password, $data->password)) {
            return response()->json([
                'message' => 'Invalid login details',
            ], 401);
        }

        $data->tokens()->delete();

        $token = $data->createToken('auth_token')->plainTextToken;

        /* Setting the status to 0. */
        $status = 0;

        if ($data) {

            $status = 1;

            (new AuthLogController)->create([
                'action'  => 'login',
                'table_name'  => 'user',
                'user_id' => $data->id,
                'token' => $token,
                'status' => $status,
                'user_type' => $data->type,
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform(),
            ]);
        }

        return response()->json([
            'access_token'  => $token,
            'token_type'    => 'Bearer',
            'status' => $status,
        ]);
    }

    public function profile()
    {

        $status = 0;

        $data = auth()->user();

        if ($data) {

            (new ActivityLogController)->create([

                'action'  => 'profile',
                'table_name'  => 'users',
                'table_id'  => auth()->user()->id,
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform()

            ]);

            $status = 1;
        }

        return response()->json([
            'data' => $data,
            'status' => $status
        ]);
    }

    public function changePassword(ChangePasswordRequest $request)
    {

        /* get all validated incoming request */
        $validated = $request->safe()->all();

        /* set status to 0 */
        $status = 0;

        /* check if the old password input is same with the current password */
        if (Hash::check($validated['password'], auth()->user()->password)) {


            $data = User::whereId(auth()->user()->id)->update(['password' => Hash::make($validated['new_password'])]);


            /* set status to 1 and create a activity log if the data returns true */
            if ($data) {

                (new ActivityLogController)->create([

                    'action'  => 'change_password',
                    'table_name'  => 'users',
                    'user_type' => auth()->user()->type,
                    'table_id'  => auth()->user()->id,
                    'before_data' =>  Hash::make($validated['password']),
                    'after_data' =>   Hash::make($validated['new_password']),
                    'user_id' => auth()->user()->id,
                    'device' =>  Agent::device(),
                    'browser' =>  Agent::browser(),
                    'platform' => Agent::platform()

                ]);

                $status = 1;
            }
        } else {

            return response()->json([

                "message" => "input your previous password",
                'status' => $status

            ]);
        };

        return response()->json([
            'status' => $status
        ]);
    }

    public function logout(Request $request)
    {
        $data = auth('user')->user()->tokens()->delete();

        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            (new AuthLogController)->create([
                'action'  => 'logout',
                'table_name'  => 'user',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'token' => $request->bearerToken(),
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform(),
            ]);
        }

        return response()->json([
            'status' => $status,
        ]);
    }
}
