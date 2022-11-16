<?php
namespace App\Traits;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser
{
	private function successResponse($data, $code)
	{
		return response()->json($data, $code);
	}

	protected function errorResponse($message, $code)
	{
		return response()->json(['error' => $message, 'code' => $code], $code);
	}

	protected function showAll(Collection $collection, $code = 200)
	{

		//$collection = $this->filterData($collection);
		//$collection = $this->sortData($collection);
		$collection = $this->paginate($collection);
		return $this->successResponse($collection, $code);
	}

	protected function showOne(Model $instance, $code = 200)
	{
		return $this->successResponse($instance, $code);
	}

	protected function showMessage($message, $code = 200)
	{
		return $this->successResponse(['data' => $message], $code);
	}

	protected function showQuery($query)
	{
		return $this->successResponse(['data' =>$query], 200);
	}

	protected function filterData(Collection $collection)
	{
		foreach (request()->query() as $query => $value) {
			if (isset($value)) {
				if($query != 'query' && $query != 'page' && $query != 'per_page'&& $query != 'sort_by'){
					if($value === 'true' || $value==='false')
					{
						$value === 'true'? $value=true: $value=false;
					}
					$collection = $collection->where($query, $value)->values();
				}
			}

		}
		//Log::notice('Información: '.$collection);
		return $collection;
	}

	protected function paginate(Collection $collection)
	{
		$rules = [
			'per_page' => 'integer|min:2|max:50'
		];

		Validator::validate(request()->all(), $rules);

		$page = LengthAwarePaginator::resolveCurrentPage();

		$perPage = 10;
		if (request()->has('per_page')) {
			$perPage = (int) request()->per_page;
		}

		$results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

		$paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
			'path' => LengthAwarePaginator::resolveCurrentPath(),
		]);

		$paginated->appends(request()->all());

		return $paginated;
	}

	protected function sortData(Collection $collection)
	{
		if (request()->has('sort_by')) {
			$attribute = (request()->sort_by);
			$collection = $collection->sortBy->{$attribute};
		}
		return $collection;
	}
}