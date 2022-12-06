<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Jenssegers\Agent\Facades\Agent;
use App\Http\Requests\Role\ListRequest;
use App\Http\Requests\Role\CreateRequest;
use App\Http\Requests\Role\UpdateRequest;
use App\Http\Requests\Role\ReadDeleteRequest;

class RoleController extends Controller
{
    public function __construct()
    {
		$this->role = new Role();
    }

	public function create(CreateRequest $request)
	{
		$data = $this->role->create($request->all());
		
		$status = 0;

		/* set status to 1 and create a activity log if the data returns true */
		if ($data) {

			$status = 1;

			$log = [
				'action'  => 'create',
				'after_data' => $data,
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
				'table_name'  => 'role',
				'table_id'  => $data->id,
				'created_at' => Carbon::now()->format('Y-m-d H:i:s'),
				'device' =>  Agent::device(),
				'browser' =>  Agent::browser(),
				'platform' => Agent::platform(),
			];

			(new ActivityLogController)->create($log);
		}

        return response()->json([
			'status' => $status
		]);
	}

    public function read(ReadDeleteRequest $request)
	{
		$data = $this->role->find($request->id);

		$status = 0;

		/* set status to 1 and create a activity log if the data returns true */
		if ($data) {

			$status = 1;

			$log = [
				'action'  => 'read',
				'table_name'  => 'role',
                'user_id' => auth()->user()->id,
                'user_type' => auth()->user()->type,
				'table_id'  => $data->id,
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
            'ability'
        ];

        $key = ($request->search_key) ? $request->search_key : '';

        if($request->search_key){
            $key = $request->search_key;
        }

        $limit = ($request->limit) ? $request->limit : 50;
        $sort_column = ($request->sort_column) ? $request->sort_column : 'created_at';
        $sort_order = ($request->sort_order) ? $request->sort_order : 'desc';

		$data = $this->role->where(function ($q) use ($orWhere_columns, $key) {
                            foreach ($orWhere_columns as $column) {
                                $q->orWhere($column, 'LIKE', "%{$key}%");
                            }
                        });

        if($request->from && $request->to){
            $data = $data->whereBetween('created_at', [Carbon::parse($request->from)->format('Y-m-d H:i:s'), Carbon::parse($request->to)->format('Y-m-d H:i:s')]);
        }

        $data = $data->orderBy($sort_column, $sort_order)->paginate($limit);

		$status = 0;

		/* set status to 1 and create a activity log if the data returns true */
		if ($data) {

			$status = 1;

			$log = [
				'action'  => 'list',
				'table_name'  => 'role',
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
		$data = Role::findorFail($validated['id']);
		$before_data = Role::findorFail($validated['id']);

		if ($data) {

			$data->update($validated);

			$status = 1;

			$log = [
				'action'  => 'update',
				'table_name'  => 'role',
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
			'status' => $status
		]);
		
	}

	public function delete(ReadDeleteRequest $request)
	{
		$status = 0;
		$validated = $request->safe()->all();
		$data = Role::findorFail($validated['id']);
		$data->delete($validated);

		if ($data) {
			$status = 1;

			$log = [
				'action'  => 'delete',
				'table_name'  => 'role',
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
