<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Facades\Agent;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\User\ListRequest;
use App\Http\Requests\User\CreateRequest;
use App\Http\Requests\User\UpdateRequest;
use App\Http\Requests\User\ReadDeleteRequest;

class UserController extends Controller
{
    public function __construct()
    {
        $this->user = new User();
    }

    public function login(Request $request)
    {
        $data = $this->user->where('username', $request['username'])->first();

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

    public function create(CreateRequest $request)
    {
        $request['password'] = Hash::make($request->password);

        $data = $this->user->create($request->all());

        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $data = [
                'action'  => 'create',
                'after_data' => $data,
                'user_id' => auth()->user()->id,
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

    public function read(ReadDeleteRequest $request)
    {
        $data = $this->user->find($request->id);

        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $log = [
                'action'  => 'read',
                'after_data' => $data,
                'table_name'  => 'user',
                'table_id'  => $data->id,
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform(),
            ];

            (new ActivityLogController)->create($log);
        }

        return response()->json([
            'data' => $data,
            'status' => $status
        ]);
    }

    public function list(ListRequest $request)
    {
        $orWhere_columns = [
            'username', 'name'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if ($request->search_key) {
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'created_at';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

        $data = $this->user->where(function ($q) use ($orWhere_columns, $key) {
            foreach ($orWhere_columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$key}%");
            }
        });

        if ($request->from && $request->to) {
            $data = $data->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $data = $data->orderBy($sort_column, $sort_order)->paginate($limit);

        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $log = [
                'action'  => 'list',
                'table_name'  => 'user',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform()

            ];

            (new ActivityLogController)->create($log);
        }

        return response()->json([
            'data' => $data,
            'status' => $status
        ]);
    }

    public function update(UpdateRequest $request)
    {
        $status = 0;
        $validated = $request->safe()->all();
        $data = User::findorFail($validated['id']);
        $before_data = User::findorFail($validated['id']);

        if ($data) {

            $data->update($validated);

            $status = 1;

            $log = [
                'action'  => 'update',
                'table_name'  => 'user',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'table_id' => $data->id,
                'before_data' => $before_data,
                'after_data' => $data,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform()

            ];

            (new ActivityLogController)->create($log);
        }

        return response()->json([
            'data' => $data,
            'status' => $status,
        ]);
    }

    public function delete(ReadDeleteRequest $request)
    {
        $status = 0;
        $validated = $request->safe()->all();
        $data = User::findorFail($validated['id']);
        $data->delete($validated);

        if ($data) {
            $status = 1;

            $log = [
                'action'  => 'delete',
                'table_name'  => 'user',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'table_id' => $data->id,
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform()

            ];

            (new ActivityLogController)->create($log);
        }

        return response()->json([
            'status' => $status,
        ]);
    }
}
