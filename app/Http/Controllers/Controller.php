<?php

namespace App\Http\Controllers;
use App\Voter as voter;


use Illuminate\Http\Request;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;


class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    
    function votersSections() {
        $paginatorLength = LengthAwarePaginator::resolveCurrentPage() * 50 - 50;
        $itemsPerPage = 50;
        $sections = voter::select('section', DB::raw('count(*) as total'))->where('id', '>', $paginatorLength)->groupBy('section')->take(50)->get();
        $sectionsCollection = new Collection($sections);
        $result = new LengthAwarePaginator($sectionsCollection, 10000000, $itemsPerPage);

        return $result;
    }

    function votersByName($params) {
        $type_condition = explode("=", $params)[0];
        $name = explode("=", $params)[1];
        $currentPage = LengthAwarePaginator::resolveCurrentPage();
        $number = $currentPage * 1000 - 1000;
        $itemsPerPage = 1000;

        switch ($type_condition) {
            case 'equal':
                $voters = voter::where('id', '>', $number)->where('name', '=', $name)->take(1000)->get();
                break;
            case 'start':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name.'%')->take(1000)->get();
                break;
            case 'include':
                $voters = voter::where('id', '>', $number)->where('name', 'like', $name)->take(1000)->get();
                break;
            default:
                return response("",404);
                break;
        }
        
        $votersCollection = new Collection($voters);
        $response = [
            'body' => $votersCollection,
            'status' => 'OK',
            'code' => 200,
            'meta' => [

            'current_page' => $currentPage,
            'total' => 10000000,
            'next_page' => '/?page='.(String)($currentPage+1),
            'prev_page' => '/?page='.(String)($currentPage-1),
            'item_per_page' => $itemsPerPage,
            'handled_by' => $_SERVER['SERVER_ADDR']
            ],
        ];
        return $response;
    }

    function voter () {
    	//$paginatedSearchResults = voter::paginate(100);
    	//var_dump($voters);
		   //die();
			//Get current page form url e.g. &page=6
			$response = [];
		$currentPage = LengthAwarePaginator::resolveCurrentPage();
			
		$number = $currentPage*100 - 100;
			
		$items = voter::where('id','>',$number)->take(100)->get();;
		

		//Create a new Laravel collection from the array data
		$collection = new Collection($items);

		//Define how many items we want to be visible in each page
		$perPage = 100;

		//Slice the collection to get the items to display in current page
		//$currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

		//Create our paginator and pass it to the view
		//$paginatedSearchResults = new LengthAwarePaginator($collection, 10000000, $perPage);
		//$paginatedSearchResults
		//echo $json;
		$response ['data']  = $collection;
		$response ['meta'] ['current_page'] =  $currentPage;
		$response ['meta'] ['total'] = 10000000 ;
		$response ['meta'] ['next_page'] =  '/?page='.(String)($currentPage+1);
		$response ['meta'] ['records_on_data'] =  $perPage;
		$response ['meta'] ['handled_by'] = $_SERVER['SERVER_ADDR'];
		return $response;
	}

	function vote (Request $request, $id) {
		//var_dump($id);
		$response ['meta'] ['handled_by'] = $_SERVER['SERVER_ADDR'];
		$voter = Voter::find($id);
		//echo ($voter);
		try {
			if($voter->has_voted == 1){
				$response ['msg'] = "Voter has already voted";

				return response($response,409);
			}else{
				$voter->has_voted = 1;
				$voter->save();
				
				return response($response,200);
			}
		} catch (Exception $e) {
			$response = "Something went wrong when processing vote";
			return response($response,409);
		}
		
	}

	function voteReset () {
		Voter::where('has_voted', 1)->update(['has_voted' => 0]);
		return response("",200);
	}
}

