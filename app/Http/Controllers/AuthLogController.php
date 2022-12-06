<?php

namespace App\Http\Controllers;

use App\Models\AuthLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Facades\Agent;
use App\Http\Requests\AuthLog\ListRequest;
use App\Http\Requests\AuthLog\ReadRequest;
use App\Http\Controllers\ActivityLogController;

class AuthLogController extends Controller
{
    public function create($data)
    {
        AuthLog::create($data);
    }

    public function read(ReadRequest $request)
    {

        /* get all validated incoming request */
        $validated = $request->safe()->only(['id']);

        /* set status to 0 */
        $status = 0;

        /* find AuthLog ID */
        $data = AuthLog::findOrFail($validated['id']);

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $log = [
                'action'  => 'get',
                'table_name'  => 'auth_logs',
                'user_id' => auth()->user()->id,
                'user_type' => $data->type,
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform()

            ];

            (new ActivityLogController)->create($log);
        }

        /* return the Role details */
        return response()->json([
            'data' => $data,
            'status' => $status,
        ]);
    }

    public function list(ListRequest $request)
    {

        $search_columns = ['action', 'table_name'];

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'id';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

        $data = new  AuthLog();

        /* Searching for the value of the request. */
        if (isset($request->search)) {
            $key = $request->search;
            $data = $data->where(function ($q) use ($search_columns, $key) {
                foreach ($search_columns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$key}%");
                }
            });
        }

        /* Filtering the data by date. */
        if ($request->from && $request->to) {
            $data = $data->whereBetween(
                'created_at',
                [
                    Carbon::parse($request->from)->format('Y-m-d H:i:s'),
                    Carbon::parse($request->to)->format('Y-m-d H:i:s')
                ]
            );
        }
        
        /*Filter data by user*/
        if ($request->user_id) {
            $data = $data->whereUserId($request->user_id);
        }

        /*Filter data by actions*/
        if ($request->table) {

            $data = $data->whereTableName($request->table);
        }

        $data = $data->orderBy($sort_column, $sort_order)->paginate($limit);

        /* Setting the status to 0. */
        $status = 0;

        /* set status to 1 and create a activity log if the data returns true */
        if ($data) {

            $status = 1;

            $log = [
                'action'  => 'list',
                'table_name'  => 'auth_logs',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
                'ip_address' => $request->ip(),
                'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
                'device' =>  Agent::device(),
                'browser' =>  Agent::browser(),
                'platform' => Agent::platform(),
                'status' => $status,

            ];

            (new ActivityLogController)->create($log);
        }

        return response()->json([
            'data' => $data,
            'status' => $status
        ]);
    }
}
